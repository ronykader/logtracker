<?php

namespace Obd\Logtracker\Console\Commands;

use Illuminate\Console\Command;
use Obd\Logtracker\Models\Logtracker;

class PruneLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logtracker:prune {--days= : Override the default retention days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove audit logs older than the configured retention period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days') ?: config('obd_tracker.retention_days', 90);
        $cutoffDate = now()->subDays($days);
        
        $this->info("Pruning logs older than {$days} days (Before {$cutoffDate->toDateString()})...");

        $totalDeleted = 0;
        $batchSize = 1000;
        $startTime = microtime(true);

        // Perform chunked deletion to avoid long table locks on high-traffic sites
        while ($deleted = Logtracker::where('log_date', '<', $cutoffDate)->limit($batchSize)->delete()) {
            $totalDeleted += $deleted;
            $this->comment("Progress: Removed {$totalDeleted} records so far...");
        }

        $duration = round(microtime(true) - $startTime, 2);

        if ($totalDeleted > 0) {
            $this->info("Successfully finished. Total records removed: {$totalDeleted} (Took {$duration}s)");
        } else {
            $this->info("No stale logs found to prune.");
        }
    }
}
