<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('d_models', function (Blueprint $table) {
            $table->id();            
            $table->string('model_name',32);
            $table->string('model_desc',280)->nullable();            
            $table->float('accuracy');
            $table->float('f1_score');
            $table->float('precision');
            $table->float('recall');
            $table->boolean('actived')->default(0);
            $table->float('execution_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('d_models');
    }
}
