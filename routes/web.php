<?php

use App\Http\Controllers\ChartController;
use App\Http\Controllers\WordCloudController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\TopicController;
use Backpack\CRUD\app\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [AdminController::class, 'dashboard'])->name('backpack.dashboard');
Route::get('klasifikasi', [AdminController::class, 'classification'])->name('backpack.classification');
Route::post('classification-process', [AdminController::class, 'classification_process'])->name('classification.process');
Route::post('/get-tweet', [AdminController::class, 'tweet'])->name('tweet');
Route::get('/admin', [AdminController::class, 'redirect'])->name('backpack');

Route::post('/chart/read-data', [ChartController::class, 'index'])->name('chart.read');
Route::post('/word-cloud', [WordCloudController::class, 'index'])->name('word.cloud');

Route::get('/topik', [TopicController::class, 'index'])->name('topik');
Route::get('/topik/load', [TopicController::class, 'load'])->name('topik.load');
Route::get('/topik/show', [TopicController::class, 'show'])->name('topik.show');
Route::get('/topik/status', [TopicController::class, 'status'])->name('topik.status');
Route::get('/topik/delete', [TopicController::class, 'delete'])->name('topik.delete');
Route::post('/topik/update', [TopicController::class, 'update'])->name('topik.update');

Route::get('/detail-topik', [TopicController::class, 'visualization'])->name('detail-topik');

Route::get('/cetak', [ExportController::class, 'index'])->name('cetak');
Route::post('/export-process', [ExportController::class, 'export'])->name('export.process');
