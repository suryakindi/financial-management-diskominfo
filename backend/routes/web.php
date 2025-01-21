<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/log/respontime', function () {
    
    $logDirectory = storage_path('logs');

    $logFiles = glob($logDirectory . '/respontime-*.log');

    
    if (!empty($logFiles)) {
        
        $latestLogFile = collect($logFiles)->sortByDesc(function ($file) {
            return filemtime($file);
        })->first();

        // Membaca isi file
        $logContent = File::get($latestLogFile);


        return response($logContent, 200)->header('Content-Type', 'text/plain');
    }


    return response("Log file not found.", 404);
});
