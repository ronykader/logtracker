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
        $enabled = config('logtracker.mongodb.enabled', false);
        if (!$enabled) {
            $this->warn('MongoDB synchronization is disabled in config/obd_tracker.php');
            return;
        }

        $batchSize = config('logtracker.mongodb.batch_size', 100);
        $connection = config('logtracker.mongodb.connection', 'mongodb');
        $collectionName = config('logtracker.mongodb.collection', 'audit_logs');
        $prune = config('logtracker.mongodb.prune_after_sync', false);

        $logs = Logtracker::where('synchronous', 0)->take($batchSize)->get();

        if ($logs->isEmpty()) {
            $this->info('No new logs to synchronize.');
            return;
        }

        try {
            $mongoCollection = DB::connection($connection)->table($collectionName);
            
            $this->info("Syncing {$logs->count()} logs to MongoDB...");

            $dataToInsert = $logs->map(function ($log) {
                $data = $log->toArray();
                // Ensure ID is treated correctly for Mongo if needed
                $data['sql_id'] = $data['id'];
                unset($data['id']);
                
                // Parse JSON fields so they are stored as objects in Mongo
                $data['users'] = json_decode($data['users'], true);
                $data['data'] = json_decode($data['data'], true);
                $data['new_data'] = json_decode($data['new_data'], true);
                
                return $data;
            })->toArray();

            $mongoCollection->insert($dataToInsert);

            $ids = $logs->pluck('id');
            
            if ($prune) {
                Logtracker::whereIn('id', $ids)->delete();
                $this->info('Logs synchronized and pruned from SQL.');
            } else {
                Logtracker::whereIn('id', $ids)->update(['synchronous' => 1]);
                $this->info('Logs synchronized and marked in SQL.');
            }

        } catch (\Exception $e) {
            $this->error('Failed to sync logs: ' . $e->getMessage());
        }
    }
}
