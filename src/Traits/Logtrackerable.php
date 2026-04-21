<?php
namespace Obd\Logtracker\Traits;

use stdClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

trait Logtrackerable
{
    static protected $logTable = 'logtrackers';
    
    
    /**
     * Log model activity to the database.
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $logType
     * @return void
     */
    static function logToDatabase($model, $logType)
    {
        // Skip logging if explicitly disabled on the model
        if (isset($model->excludeLogging) && $model->excludeLogging) {
            return;
        }

        // Use toArray() to respect Eloquent's $hidden attributes
        $data = $model->toArray();
        
        // Remove specifically excluded fields if defined on the model
        if (isset($model->log_except) && is_array($model->log_except)) {
            foreach ($model->log_except as $field) {
                unset($data[$field]);
            }
        }

        $formattedOriginal = json_encode($data);
        
        // For 'edit', we want to capture what exactly changed if possible
        $newData = '';
        if ($logType == 'edit') {
            $newData = $formattedOriginal;
            // Get original version with hidden attributes respected
            $originalArray = $model->getOriginal();
            if (isset($model->log_except) && is_array($model->log_except)) {
                foreach ($model->log_except as $field) {
                    unset($originalArray[$field]);
                }
            }
            // Manually filter hidden fields from the raw original array
            $hidden = $model->getHidden();
            foreach ($hidden as $hide) {
                unset($originalArray[$hide]);
            }
            
            $formattedOriginal = json_encode($originalArray);
        }

        $tableName = $model->getTable();
        $dateTime = date('Y-m-d H:i:s');

        // Resolve User Information
        $sessionUser = Session::get('user');
        $authUser = auth()->user();
        
        $userId = $authUser->id ?? $sessionUser['id'] ?? 1;
        
        // Generic user data capture
        $user_array = [
            'id' => $userId,
            'name' => $authUser->name ?? $sessionUser['userName'] ?? 'System',
            'email' => $authUser->email ?? '',
        ];
        
        // Include additional metadata if it exists on the user model without hardcoding requirements
        $optionalFields = ['designation', 'officeNameEng', 'officeNameBng'];
        foreach ($optionalFields as $field) {
            if ($authUser && isset($authUser->$field)) {
                $user_array[$field] = $authUser->$field;
            } elseif (isset($sessionUser[$field])) {
                $user_array[$field] = $sessionUser[$field];
            }
        }

        $userInfo = json_encode($user_array);

        $logData = [
            'users'       => $userInfo,
            'user_id'     => $userId,
            'log_date'    => $dateTime,
            'table_name'  => $tableName,
            'log_type'    => $logType,
            'new_data'    => $newData,
            'data'        => $formattedOriginal,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'url'         => request()->fullUrl(),
            'route_name'  => request()->route() ? request()->route()->getName() : null,
            'synchronous' => 0,
        ];

        // Check if queued logging is enabled
        if (config('obd_tracker.queue_enabled', false)) {
            \Obd\Logtracker\Jobs\LogTrackerJob::dispatch($logData)
                ->onQueue(config('obd_tracker.queue_name', 'default'));
        } else {
            DB::table(self::$logTable)->insert($logData);
        }
    }

    
    public static function bootLogtrackerable()
    {
        // When data updated
        self::updated(function ($model) {
            self::logToDatabase($model, 'edit');
        });

        // When Data deleted
        self::deleted(function ($model) {
            self::logToDatabase($model, 'delete');
        });


        // When data Created
        self::created(function ($model) {
            self::logToDatabase($model, 'create');
        });
        

    }
}
