<?php

namespace App\Imports;

use App\Models\Dataset;
use App\Preprocessing\PreprocessingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DatasetsImport implements ToModel, WithValidation, WithBatchInserts, WithHeadingRow, WithChunkReading, ShouldQueue, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsFailures, SkipsErrors;    

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $prepro_train = PreprocessingService::index([$row['text']]);                

        return new Dataset([
            'text' => $row['text'],
            'text_prepro' => $prepro_train[0]['result'],            
            'label' => strtolower($row['label']),
            'type' => strtolower($row['type'])
        ]);
    }

    public function rules(): array
    {
        return [
            'text' => 'required|max:320|unique:datasets',
            'label' => 'required|in:senang,sedih,marah,cinta,takut',
            'type' => 'required|in:training,testing'
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
