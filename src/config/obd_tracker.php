<?php 

return [
    'service_audit' => 'rqsv9PuZzGh41FyBt6fVNC27dn5U3RI0YQM8bxWmokeJiwpHKjTDAgEOSLXcla1636542115',
    'route_prefix' => 'audit-panel',
    'api_prefix' => 'api/audit-panel-data',
    'ui_middleware' => ['web', 'auth'],
    'api_middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | MongoDB Synchronization Settings
    |--------------------------------------------------------------------------
    |
    | If enabled, logs can be backed up to a MongoDB instance in the background
    | using the `php artisan logtracker:sync-mongo` command.
    |
    */
    'mongodb' => [
        'enabled' => env('LOGTRACKER_MONGO_ENABLED', false),
        'connection' => env('LOGTRACKER_MONGO_CONNECTION', 'mongodb'),
        'collection' => env('LOGTRACKER_MONGO_COLLECTION', 'audit_logs'),
        'prune_after_sync' => env('LOGTRACKER_MONGO_PRUNE', false),
        'batch_size' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | If queue is enabled, logging activity will be dispatched to a background 
    | job instead of blocking the current user request.
    |
    */
    'queue_enabled' => env('LOGTRACKER_QUEUE_ENABLED', false),
    'queue_name' => env('LOGTRACKER_QUEUE_NAME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Manual Access Control
    |--------------------------------------------------------------------------
    | List of User IDs allowed to access the audit panel. 
    | Example: '1,5,10' in .env will allow users with those IDs.
    | If empty, any authenticated user can access.
    */
    'allowed_user_ids' => env('LOGTRACKER_ALLOWED_IDS') ? explode(',', env('LOGTRACKER_ALLOWED_IDS')) : [],

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    | The number of days to keep logs before they are considered stale.
    | The `php artisan logtracker:prune` command uses this value.
    |
    */
    'retention_days' => env('LOGTRACKER_RETENTION_DAYS', 90),
];