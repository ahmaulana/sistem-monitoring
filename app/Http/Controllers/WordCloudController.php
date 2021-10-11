<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use Carbon\Carbon;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Other\Tokenizers\NGram;
use Rubix\ML\Transformers\TfIdfTransformer as TransformersTfIdfTransformer;
use Rubix\ML\Transformers\WordCountVectorizer;

class WordCloudController extends Controller
{
    public function index()
    {
        $start_date = Carbon::parse(request()->startDate);
        $end_date = Carbon::parse(request()->endDate);
        $count_days = $start_date->diffInDays($end_date) + 1;
        $emotion = request()->emotion;

        $total_data_per_day = 10000 / $count_days;

        $date = Carbon::parse($start_date)->toDateTimeString();

        //Data is empty
        $total_data = Tweet::whereDate('created_at', $date)->count();        

        if ($total_data > 0) {
            for ($i = 0; $i < $count_days; $i++) {

                $words[] = Tweet::select('word_cloud', 'emotion')->join('classifications', 'tweets.id', 'tweet_id')->where('emotion', $emotion)->whereDate('created_at', $date)->take($total_data_per_day)->get()->toArray();
                $date = Carbon::parse($date)->addDay(1)->toDateTimeString();
            }

            $tweets = array_merge([], ...$words);

            foreach ($tweets as $tweet) {
                $tweetSamples[] = [$tweet['word_cloud']];
                $tweetLabels[] = $tweet['emotion'];
            }
            $wordCountVectorizer = new WordCountVectorizer(10000, 1, 10000, new NGram(1, 1));
            $tfidf = new TransformersTfIdfTransformer();
            $training = Labeled::build($tweetSamples, $tweetLabels)
                ->apply($wordCountVectorizer)
                ->apply($tfidf);

            $topKeyword = array();
            foreach ($training->samples() as $rows) {
                foreach ($rows as $key => $row) {
                    array_key_exists($key, $topKeyword) ? $topKeyword[$key] += $row : $topKeyword[$key] = $row;
                }
            }
            foreach ($topKeyword as $key => $value) {
                $keyword_score[$key]['word'] = $wordCountVectorizer->vocabularies()[0][$key];
                $keyword_score[$key]['score'] = $value;
            }

            //Sort higher to lower
            usort($keyword_score, function ($a, $b) {
                return $a['score'] <=> $b['score'];
            });

            $keyword_score = array_reverse($keyword_score);
            $i = 0;

            do {
                if ($keyword_score[$i]['word'] == 'covid' || $keyword_score[$i]['word'] == 'corona' || strlen($keyword_score[$i]['word']) <= 3) {
                    array_splice($keyword_score, $i, 1);
                } else {
                    $i++;
                }
            } while ($i < array_key_last($keyword_score));

            $sum = 0;
            $totalKeyword = 20;
            $limit = count($keyword_score) < $totalKeyword ? count($keyword_score) : $totalKeyword;
            for ($i = 0; $i < $limit; $i++) {
                $result[] = [$keyword_score[$i]['word'], $keyword_score[$i]['score']];
                $sum += $keyword_score[$i]['score'];
            }            
            foreach ($result as $key => $value) {
                $result[$key][1] = $result[$key][1] / $sum * 600;
            }
            return $result;
        } else {
            return false;
        }
    }
}
