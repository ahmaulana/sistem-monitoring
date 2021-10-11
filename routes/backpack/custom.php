<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.
// Route::crud('/', 'PublicController');
Route::group([    
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web')        
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes    
    Route::crud('tweet', 'TweetCrudController');    
    Route::get('charts/topic-visualization', 'Charts\TopicVisualizationChartController@response')->name('charts.topic-visualization.index');
}); // this should be the absolute last line of this file
Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('dataset', 'DatasetCrudController');
    Route::crud('dmodel', 'DModelCrudController');
    Route::crud('stopword', 'StopwordCrudController');
    Route::crud('keyword', 'KeywordCrudController');
    Route::get('charts/daily-emotions', 'Charts\DailyEmotionsChartController@response')->name('charts.daily-emotions.index');
}); // this should be the absolute last line of this file