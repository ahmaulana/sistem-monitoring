<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    public function index()
    {
        $start_date = Carbon::parse(request()->startDate);
        $end_date = Carbon::parse(request()->endDate);
        $count_days = $start_date->diffInDays($end_date) + 1;
        $date = Carbon::parse($start_date)->toDateTimeString();
        for ($i = 0; $i < $count_days; $i++) {
            $emotion_list = Classification::select('emotion', DB::raw('count(*) as total'))
                ->join('tweets','tweet_id','tweets.id')->whereDate('created_at', '=', $date)->orderBy('emotion','DESC')->groupBy('emotion')
                ->get();
            $emotions[0][] = (isset($emotion_list[0]->total) ? $emotion_list[0]->total : 0);
            $emotions[1][] = (isset($emotion_list[1]->total) ? $emotion_list[1]->total : 0);
            $emotions[2][] = (isset($emotion_list[2]->total) ? $emotion_list[2]->total : 0);
            $emotions[3][] = (isset($emotion_list[3]->total) ? $emotion_list[3]->total : 0);
            $emotions[4][] = (isset($emotion_list[4]->total) ? $emotion_list[4]->total : 0);
            $label[] = $date;
            $date = Carbon::parse($date)->addDay(1)->toDateTimeString();
        }
        return ['label' => $label,'emotion' => $emotions];
    }
}
