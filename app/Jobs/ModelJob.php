<?php

namespace App\Jobs;

use App\Models\Dataset;
use App\Models\DModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Rubix\ML\Classifiers\GaussianNB;
use Rubix\ML\CrossValidation\Reports\AggregateReport;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Other\Tokenizers\NGram;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\TfIdfTransformer;
use Rubix\ML\Transformers\WordCountVectorizer;
use stdClass;

class ModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {        
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $start_time = microtime(true);
        $trainingData = Dataset::select('text_prepro', 'label')->where('type', 'training')->get();
        $testingData = Dataset::select('text_prepro', 'label')->where('type', 'testing')->get();        

        //Testing
        //Count Emotion
        $emotions = ['senang', 'sedih', 'marah', 'cinta', 'takut'];        
        foreach ($emotions as $key => $emotion) {            
            $total = Dataset::select('text_prepro', 'label')->where('type', 'training')->where('label', $emotion)->count();

            //10% Training for Testing            
            $take = floor(0.15 * $total);

            //Starting Point
            $start = $total - $take - 1;

            $concat[$key] = Dataset::select('text_prepro', 'label')->where('type','training')->where('label', $emotion)->skip($start)->take($take)->get();
        }                                        

        //Training
        foreach ($trainingData as $value) {
            $trainingSamples[] = [$value->text_prepro];
            $trainingLabels[] = $value->label;
        }

        //Testing
        $testings = $testingData->concat($concat[0])->concat($concat[1])->concat($concat[2])->concat($concat[3])->concat($concat[4]);

        foreach ($testings as $value) {
            $testingSamples[] = [$value->text_prepro];
            $testingLabels[] = $value->label;
        }

        $training = Labeled::build($trainingSamples, $trainingLabels);
        $testing = Labeled::build($testingSamples, $testingLabels);

        $estimator = new PersistentModel(
            new Pipeline([
                new WordCountVectorizer(10000, 3, 10000, new NGram(1, 2)),
                new TfIdfTransformer(),
            ], new GaussianNB()),
            new Filesystem(storage_path() . '/model/' . $this->data['model_name'] . '.model', true)
        );

        $estimator->train($training);

        $predictions = $estimator->predict($testing);

        //Report    
        $report = new AggregateReport([
            new MulticlassBreakdown(),
            new ConfusionMatrix(),
        ]);
        $results = $report->generate($predictions, $testing->labels());
        $estimator->save();

        //end time
        $end_time = microtime(true);
        $execution_time = $end_time-$start_time;
        
        //Check Active Model
        $actived = DModel::count() == 0 ? 1 : 0;

        //Save to DB
        $fix_model = DModel::create([
            'model_name' => $this->data['model_name'],
            'model_desc' => $this->data['model_desc'],
            'accuracy' => $results[0]['overall']['accuracy'],
            'f1_score' => $results[0]['overall']['f1_score'],
            'precision' => $results[0]['overall']['precision'],
            'recall' => $results[0]['overall']['recall'],
            'execution_time' => $execution_time,
            'actived' => $actived
        ]);
    }
}
