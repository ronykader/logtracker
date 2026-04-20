<?php

namespace Obd\Logtracker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class LogTrackerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $logData;

    /**
     * Create a new job instance.
     *
     * @param array $logData
     */
    public function __construct(array $logData)
    {
        $this->logData = $logData;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            DB::table(config('obd_tracker.table', 'logtrackers'))->insert($this->logData);
        } catch (\Exception $e) {
            \Log::error("LogTrackerJob Failed: " . $e->getMessage(), [
                'log_data' => $this->logData
            ]);
        }
    }
}
