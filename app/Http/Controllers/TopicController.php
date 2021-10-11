<?php

namespace App\Http\Controllers;

use Backpack\CRUD\app\Library\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TopicController extends Controller
{
    public function index()
    {
        if(!backpack_auth()->check()){
            return redirect(route('backpack.auth.login'));
        }
        return view(backpack_view('topic'));
    }

    public function visualization()
    {
        Widget::add([ 
            'type'       => 'chart',
            'controller' => \App\Http\Controllers\Admin\Charts\TopicVisualizationChartController::class,
        
            // OPTIONALS
        
            'class'   => 'card mb-2',
            'wrapper' => ['class'=> 'col-md-12'] ,
            'content' => [
                 'header' => 'Topik Populer', 
                 'body'   => 'Distribusi topik berkaitan dengan pandemi COVID-19.<br><br>',
            ],
        ])->to('before_content');
        return view(backpack_view('topic_visualization'));
    }

    public function chart()
    {
        Widget::add([ 
            'type'       => 'chart',
            'controller' => \App\Http\Controllers\Admin\Charts\TopicVisualizationChartController::class,
        
            // OPTIONALS
        
            // 'class'   => 'card mb-2',
            // 'wrapper' => ['class'=> 'col-md-6'] ,
            // 'content' => [
                 // 'header' => 'New Users', 
                 // 'body'   => 'This chart should make it obvious how many new users have signed up in the past 7 days.<br><br>',
            // ],
        ]);
    }

    public function load()
    {
        if(!backpack_auth()->check()){
            return redirect(route('backpack.auth.login'));
        }
        $topics = DB::table('topic_models')->get();
        $i=1;
        foreach($topics as $topic){
            $even_odd = $i % 2 != 0 ? 'odd' : 'even';
            $status = $topic->status == 0 ? 'Activate' : 'Running';
            $btn_disabled = $topic->status != 0 ? 'disabled-link' : '';
            $i++;
            echo '
                <tr class="'. $even_odd .'">
                    <td class="dtr-control">
                        <span>'. $topic->name .'</span>
                    </td>
                    <td>
                        <span>'. $topic->status .'</span>
                    </td>
                    <td>
                        <span></span>
                    </td>
                    <td>
                        <a class="btn btn-sm btn-link '. $btn_disabled .'" onclick="updateStatus(this, '. $topic->id .')" href="javascript:void(0)"><i class="la la-eye"></i> <span class="status">'. $status .'</span></a>

                        <!-- Single edit button -->
                        <a href="javascript:void(0)" onclick="showEntry('. $topic->id .')" class="btn btn-sm btn-link"><i class="la la-edit"></i> Open & Edit</a>

                        <a href="javascript:void(0)" onclick="deleteEntry(this,'. $topic->id .')" class="btn btn-sm btn-link delete-btn '. $btn_disabled .'" data-button-type="delete"><i class="la la-trash"></i> Delete</a>

                    </td>
                </tr>
            ';
        }
    }

    public function show()
    {
        if(!backpack_auth()->check()){
            return redirect(route('backpack.auth.login'));
        }
        $id = request()->id;
        $topics = DB::table('topic_models')->where('topic_models.id', $id)->join('topic_details', 'topic_models.id', '=', 'topic_details.topic_model_id')->get();

        foreach($topics as $topic){
            echo
                '<div class="col-8">
                    <input type="text" placeholder="Daftar kata..." value="' . $topic->text_list . '" readonly>
                </div>
                <div class="col">
                    <input type="text" class="topic_model_id" value="' . $topic->topic_model_id . '" hidden>
                    <input type="text" class="topic" placeholder="nama topik..." value="' . $topic->topic_name . '" >
                </div>';
        }
    }

    public function status()
    {
        if(!backpack_auth()->check()){
            return redirect(route('backpack.auth.login'));
        }
        $id = request()->id;
        DB::table('topic_models')->where('status', 1)->update(['status' => 0]);
        DB::table('topic_models')->where('id', $id)->update(['status' => 1]);
    }

    public function update()
    {
        if(!backpack_auth()->check()){
            return redirect(route('backpack.auth.login'));
        }
        $i = 1;
        foreach(request()->topics as $value){
            DB::table('topic_details')->where('topic_model_id', request()->id)->where('topic_id', $i)->update(['topic_name' => $value]);
            $i++;
        }
    }

    public function delete()
    {
        if(!backpack_auth()->check()){
            return redirect(route('backpack.auth.login'));
        }
        $model_id = request()->id;
        DB::table('topic_models')->where('id', $model_id)->delete();
    }
}
