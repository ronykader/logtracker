# Logtracker - Modern Audit Logging for Laravel

A high-performance, internationalized activity log manager for Laravel applications. This package tracks all database activities and provides a premium, animated audit panel.

## Features
- **Premium UI**: Glassmorphic, React-powered dashboard with **30-Day Activity Heatmaps** and interactive **Date Range Picker**.
- **Log Management**: Full-featured **System Log Viewer** with support for multiple files (daily logs), entry deletion, and bulk clearing.
- **i18n Ready**: Full support for English and Bengali locales.
- **Granular Security**: Access control via User ID whitelisting.
- **Performance**: Asynchronous logging support via Laravel Queues and **Optimized Batch Pruning**.
- **NoSQL Backup**: Background synchronization of logs to MongoDB.
- **Privacy**: Automatically masks sensitive `$hidden` attributes.

## Installation

### Local Development
Add the path to your `composer.json`:
```json
"repositories": [
    {
        "type": "path",
        "url": "./Packages/logtracker"
    }
]
```
Then run:
```bash
composer require obd/logtracker:dev-master
```

## Setup
1. **Publish Assets**:
   ```bash
   php artisan vendor:publish --tag=logtracker-config --force
   php artisan vendor:publish --tag=logtracker-views --force
   ```

2. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

3. **Add Trait to Models**:
   Automatically add the tracking trait to all your models:
   ```bash
   php artisan logtracker:install-trait
   ```

   *Alternatively, manually add to specific models:*
   ```php
   use Obd\Logtracker\Traits\Logtrackerable;

   class Project extends Model {
       use Logtrackerable;
   }
   ```

## Configuration (.env)

### Authorization
By default, the audit panel is restricted. Add allowed User IDs:
```bash
LOGTRACKER_ALLOWED_IDS=1,2,5
```

### Performance & Features
```bash
# Performance
LOGTRACKER_QUEUE_ENABLED=true

# MongoDB Sync
LOGTRACKER_MONGO_ENABLED=true
LOGTRACKER_MONGO_CONNECTION=mongodb

# Data Retention
LOGTRACKER_RETENTION_DAYS=90
```

### Synchronize logs to MongoDB:
```bash
php artisan logtracker:sync-mongo
```

### Install Trait Automatically:
Scan your model directory and inject the `Logtrackerable` trait:
```bash
php artisan logtracker:install-trait

# Preview changes without modifying files:
php artisan logtracker:install-trait --dry-run
```

### Prune Stale Logs:
Keep your database lean by removing logs older than the configured retention period:
```bash
php artisan logtracker:prune

# Or override the default retention
php artisan logtracker:prune --days=30
```

### Automated Maintenance
It is highly recommended to schedule the pruning command:
```php
$schedule->command('logtracker:prune')->daily();
```

## Updating
When updating the package to a new version, always run the following to sync the latest UI assets:
```bash
composer update obd/logtracker
php artisan vendor:publish --tag=logtracker-assets --force
php artisan optimize:clear
```
