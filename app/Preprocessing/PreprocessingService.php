<?php

namespace App\Preprocessing;

use Sastrawi\Stemmer\StemmerFactory;
use Sastrawi\StopWordRemover\StopWordRemoverFactory;

class PreprocessingService
{
    public static function index(array $tweets)
    {                
        //PREPROCESSING
        $stemmerFactory = new StemmerFactory();        
        $stopWordFactory = new StopWordRemoverFactory();
        $stemmer  = $stemmerFactory->createStemmer();
        $stopword = $stopWordFactory->createStopWordRemover();        
        

        //REMOVE URL # @
        $regex = "/\b((https?|ftp|file):\/\/|www\.)[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i";

        foreach($tweets as $key => $tweet){
            $text = preg_replace(array($regex, '/@\w+\s*/'), '', $tweet);            

            //Remove Symbol
            $remove_symbol = strtolower(preg_replace("/[^a-zA-Z\s]/", " ", $text));            

            //STOPWORD REMOVAL
            $remove_stopword = preg_replace("/\s\s+/", " ",$stopword->remove($remove_symbol));            
                    
            //STEMMING
            $stemming =  $stemmer->stem($remove_stopword);            

            //FINAL PREPROCESSING
            $tweet_after_prepro [$key]['word_cloud'] = $remove_stopword;
            $tweet_after_prepro [$key]['result'] = $stemming;            
        }        
        return $tweet_after_prepro;
    }
}