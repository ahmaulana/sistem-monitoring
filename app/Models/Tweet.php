<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    public $timestamps = false;
    
    public function classification()
    {
        return $this->hasOne(Classification::class);
    }    

    public function d_model()
    {
        return $this->hasOne(DModel::class);
    }

    public function delete()
    {
        $this->classification()->delete();

        return parent::delete();
    }
}
