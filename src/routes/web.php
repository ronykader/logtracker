<?php 

use Illuminate\Support\Facades\Route;
use Obd\Logtracker\Http\Controllers\LogtrackerController;

Route::group([
    'prefix' => config('obd_tracker.api_prefix', 'api/audit-panel-data'),
    'middleware' => config('obd_tracker.api_middleware', ['web', 'auth']),
], function () {
    Route::get('/', [LogtrackerController::class,'logApidata']);
    Route::get('/insights', [LogtrackerController::class, 'getInsights']);
});

Route::group([
    'prefix' => config('obd_tracker.route_prefix', 'audit-panel'),
    'middleware' => config('obd_tracker.ui_middleware', ['web', 'auth']),
], function () {
    Route::get('/', [LogtrackerController::class, 'index'])->name('logtracker.index');
    Route::get('/insights', [LogtrackerController::class, 'insights'])->name('logtracker.insights');
    Route::get('/system-logs', [LogtrackerController::class, 'systemLogs'])->name('logtracker.system-logs');
    Route::get('/system-log-data', [LogtrackerController::class, 'getSystemLogData'])->name('logtracker.system-log-data');
    Route::post('/system-log-clear', [LogtrackerController::class, 'clearSystemLog'])->name('logtracker.system-log-clear');
    Route::post('/system-log-delete', [LogtrackerController::class, 'deleteSystemLogEntries'])->name('logtracker.system-log-delete');
    Route::get('/ui-config', [LogtrackerController::class, 'getConfig'])->name('logtracker.ui-config');
});
