<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Dataset extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['text','text_prepro','label','type'];

    public function downloadTemplate($crud = false)
    {
        $url = Storage::url('template_dataset.xlsx');
        return '<a class="btn btn-xs btn-default" target="_blank" href="'. $url .'" data-toggle="tooltip" title="Download template file"><i class="fa fa-search"></i> Download Template</a>';
    }
}
