<?php

namespace App\Console\Commands;

use App\Models\Dataset;
use Illuminate\Console\Command;
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

class CreateModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'model:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
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

        // dd(count($testings));

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
            new Filesystem(storage_path() . '/model/test.model', true)
        );

        $estimator->train($training);

        $predictions = $estimator->predict($testing);

        //Report    
        $report = new AggregateReport([
            new MulticlassBreakdown(),
            new ConfusionMatrix(),
        ]);
        $results = $report->generate($predictions, $testing->labels());        
        dd($results);

        //end time
        $end_time = microtime(true);                    
    }
}
