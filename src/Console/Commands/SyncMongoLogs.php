<?php

namespace Obd\Logtracker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Obd\Logtracker\Models\Logtracker;

class SyncMongoLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logtracker:sync-mongo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize audit logs from SQL to MongoDB backup storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $enabled = config('obd_tracker.mongodb.enabled', false);
        if (!$enabled) {
            $this->warn('MongoDB synchronization is disabled in config/obd_tracker.php');
            return;
        }

        $batchSize = config('obd_tracker.mongodb.batch_size', 100);
        $connection = config('obd_tracker.mongodb.connection', 'mongodb');
        $collectionName = config('obd_tracker.mongodb.collection', 'audit_logs');
        $prune = config('obd_tracker.mongodb.prune_after_sync', false);

        $totalSynced = 0;
        $this->info("Starting MongoDB synchronization...");

        try {
            $mongoCollection = DB::connection($connection)->table($collectionName);

            while (true) {
                $logs = Logtracker::where('synchronous', 0)->take($batchSize)->get();
                
                if ($logs->isEmpty()) {
                    break;
                }

                $dataToInsert = $logs->map(function ($log) {
                    $data = $log->toArray();
                    $data['sql_id'] = $data['id'];
                    unset($data['id']);
                    
                    // Parse JSON fields safely
                    $data['users'] = is_string($data['users']) ? json_decode($data['users'], true) : $data['users'];
                    $data['data'] = is_string($data['data']) ? json_decode($data['data'], true) : $data['data'];
                    $data['new_data'] = is_string($data['new_data']) ? json_decode($data['new_data'], true) : $data['new_data'];
                    
                    return $data;
                })->toArray();

                $mongoCollection->insert($dataToInsert);

                $ids = $logs->pluck('id');
                
                if ($prune) {
                    Logtracker::whereIn('id', $ids)->delete();
                } else {
                    Logtracker::whereIn('id', $ids)->update(['synchronous' => 1]);
                }

                $totalSynced += $logs->count();
                $this->comment("Synced {$totalSynced} logs...");
            }

            if ($totalSynced > 0) {
                $this->info("Successfully synchronized {$totalSynced} logs to MongoDB.");
            } else {
                $this->info("No new logs found to synchronize.");
            }

        } catch (\Exception $e) {
            $this->error('Failed to sync logs: ' . $e->getMessage());
        }
    }
}
