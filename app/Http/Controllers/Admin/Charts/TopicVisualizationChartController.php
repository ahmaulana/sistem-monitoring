<?php

namespace App\Http\Controllers\Admin\Charts;

use Backpack\CRUD\app\Http\Controllers\ChartController;
use ConsoleTVs\Charts\Classes\Echarts\Chart;
use Illuminate\Support\Facades\DB;

/**
 * Class TopicVisualizationChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TopicVisualizationChartController extends ChartController
{
    public function setup()
    {
        $topics = DB::table('topics')->select('topic_name', DB::raw('count(topic_name) total'))->join('topic_details', 'topic_details.id', 'topic_detail_id')->groupBy('topic_name')->get();

        $topics = $topics->toArray();        

        usort($topics, function($a, $b) {
            return $b->total <=> $a->total;
        });

        
        $label = $total = [];
        $sum_total = 0;
        foreach($topics as $key => $topic){
            if($key > 2){
                $sum_total = $sum_total + $topic->total;
            } else {
                $label[] = $topic->topic_name;
                $total[] = $topic->total;
            }
        }

        $total[] = $sum_total;
        $label[] = 'Lainnya';
        
        $this->chart = new Chart();

        $this->chart->dataset('Red', 'pie', $total)
                    ->color([
                        'rgb(70, 127, 208)',
                        'rgb(77, 189, 116)',
                        'rgb(96, 92, 168)',
                        'rgb(255, 193, 7)',
                    ]);

        // OPTIONAL
        $this->chart->displayAxes(false);
        $this->chart->displayLegend(true);

        // MANDATORY. Set the labels for the dataset points
        $this->chart->labels($label);
    }

    /**
     * Respond to AJAX calls with all the chart data points.
     *
     * @return json
     */
    // public function data()
    // {
    //     $users_created_today = \App\User::whereDate('created_at', today())->count();

    //     $this->chart->dataset('Users Created', 'bar', [
    //                 $users_created_today,
    //             ])
    //         ->color('rgba(205, 32, 31, 1)')
    //         ->backgroundColor('rgba(205, 32, 31, 0.4)');
    // }
}