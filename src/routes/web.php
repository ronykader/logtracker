<?php 

use Illuminate\Support\Facades\Route;
use Obd\Logtracker\Http\Controllers\LogtrackerController;

Route::group([
    'prefix' => config('logtracker.api_prefix', 'api/audit-panel-data'),
    'middleware' => config('logtracker.api_middleware', ['web', 'auth']),
], function () {
    Route::get('/', [LogtrackerController::class,'logApidata']);
});

Route::group([
    'prefix' => config('logtracker.route_prefix', 'audit-panel'),
    'middleware' => config('logtracker.ui_middleware', ['web', 'auth']),
], function () {
    Route::get('/', [LogtrackerController::class, 'index']);
});
