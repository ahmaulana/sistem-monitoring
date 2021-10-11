<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Crawling\CrawlingService;
use Illuminate\Console\Command;

class TweetCrawl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweet:crawl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl tweet';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {   
        if(Keyword::count() > 0){
            $active_keyword = Keyword::select('keyword')->where('status',1)->first();
            $keyword = $active_keyword->keyword;
        } else {
            $keyword = 'COVID';
        }        
        CrawlingService::index($keyword,100);
    }
}