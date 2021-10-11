<?php

namespace App\Crawling;

use App\Events\ChartEvent;
use App\Models\Classification;
use App\Models\DModel;
use App\Models\Tweet;
use App\Preprocessing\PreprocessingService;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Thujohn\Twitter\Facades\Twitter;

class CrawlingService
{
    public static function index($keyword, $count)
    {
        //Last ID
        $last_id = Tweet::select('post_id')->orderBy('id', 'DESC')->first();

        if ($last_id == null) {
            $tweets = Twitter::getSearch(['q' => $keyword . ' -RT -filter:links', 'tweet_mode' => 'extended', 'lang' => 'id', 'count' => $count, 'format' => 'array']);
        } else {
            $tweets = Twitter::getSearch(['q' => $keyword . ' -RT -filter:links -filter:replies', 'tweet_mode' => 'extended', 'lang' => 'id', 'count' => $count, 'since_id' => intval($last_id['post_id']), 'format' => 'array']);
        }

        foreach (array_reverse($tweets['statuses']) as $key => $value) {
            //Change format date and Time Zone to Asia/Jakarta
            $dt = DateTime::createFromFormat('D M d H:i:s P Y', $value['created_at'])->setTimezone(new DateTimeZone('Asia/Jakarta'));

            $filter_tweet[$key]['post_id'] = $value['id_str'];
            $filter_tweet[$key]['username'] = $value['user']['name'];
            $filter_tweet[$key]['tweet'] = $value['full_text'];
            $filter_tweet[$key]['created_at'] = $dt->format('Y-m-d H:i:s');
            $full_text[] = $value['full_text'];
        }

        $tweet_after_prepro = PreprocessingService::index($full_text);

        //Last record for pusher
        $last = Tweet::select("created_at")->latest()->first();

        //Choose model
        if (Dmodel::count() > 1) {
            $active_model = DModel::select('id', 'model_name')->where('actived', 1)->first();
            $model = $active_model->model_name;
        } else {
            $model = 'default';
        }
        $estimator = PersistentModel::load(new Filesystem(storage_path() . '/model/' . $model . '.model'));
        if (count($filter_tweet) === count($tweet_after_prepro)) {
            foreach ($filter_tweet as $key => $tweet) {
                if (strlen($tweet['tweet']) <= 280) {
                    // Classification
                    $emotion = $estimator->predictSample([$tweet_after_prepro[$key]['result']]);

                    // Topic Classification
                    $response = Http::get('http://127.0.0.1:5000/predict',[
                        'tweet' => $tweet_after_prepro[$key]['result']
                    ]);

                    $topic = $response->json()['topic'];

                    if($emotion){
                        DB::transaction(function() use($tweet,$tweet_after_prepro,$key,$active_model,$emotion,$topic){
                            $insert_tweet = new Tweet;
                            $insert_classification = new Classification;
                            $insert_tweet->post_id = $tweet['post_id'];
                            $insert_tweet->username = $tweet['username'];
                            $insert_tweet->tweet = $tweet['tweet'];
                            $insert_tweet->tweet_prepro = $tweet_after_prepro[$key]['result'];
                            $insert_tweet->word_cloud = $tweet_after_prepro[$key]['word_cloud'];
                            $insert_tweet->created_at = $tweet['created_at'];
                            $insert_tweet->save();

                            $id = DB::getPdo()->lastInsertId();
        
                            $insert_classification->model_id = $active_model->id;
                            $insert_classification->emotion = $emotion;
                            $insert_tweet->classification()->save($insert_classification);

                            DB::table('topics')->insert([
                                'tweet_id' => $id,
                                'topic_detail_id' => $topic
                            ]);
                        });
                    }
                }
            }
        }
        //Count The Emotions
        $now = Carbon::now()->toDateTimeString();
        $emotion_list = Classification::select('emotion', DB::raw('count(*) as total'))
            ->join('tweets', 'tweet_id', 'tweets.id')->whereBetween('created_at', [$last->created_at, $now])->orderBy('emotion', 'DESC')->groupBy('emotion')
            ->get();
        $emotions[] = (isset($emotion_list[0]->total) ? $emotion_list[0]->total : 0);
        $emotions[] = (isset($emotion_list[1]->total) ? $emotion_list[1]->total : 0);
        $emotions[] = (isset($emotion_list[2]->total) ? $emotion_list[2]->total : 0);
        $emotions[] = (isset($emotion_list[3]->total) ? $emotion_list[3]->total : 0);
        $emotions[] = (isset($emotion_list[4]->total) ? $emotion_list[4]->total : 0);

        //Total Emotion
        $total_emotion = Classification::select(DB::raw('count(*) as total'))
            ->orderBy('emotion', 'DESC')->groupBy('emotion')
            ->get()->toArray();
        event(new ChartEvent(['label' => Carbon::now()->addSecond(30)->toDateTimeString(), 'emotion' => $emotions, 'total' => $total_emotion]));
    }
}
