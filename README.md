# Logtracker - Modern Audit Logging for Laravel

A high-performance, internationalized activity log manager for Laravel applications. This package tracks all database activities and provides a premium, animated audit panel.

## Features
- **Premium UI**: Glassmorphic, React-powered dashboard with **30-Day Activity Heatmaps** and interactive **Date Range Picker**.
- **Log Management**: Full-featured **System Log Viewer** with support for multiple files (daily logs), entry deletion, and bulk clearing.
- **Universal SPA**: Works in full-stack Laravel apps, API-only backends, and even as a standalone UI hosted separately.
- **i18n Ready**: Full support for English and Bengali locales.
- **Granular Security**: Access control via User ID whitelisting.
- **Performance**: Asynchronous logging support via Laravel Queues and **Optimized Batch Pruning**.
- **NoSQL Backup**: Background synchronization of logs to MongoDB.
- **Privacy**: Automatically masks sensitive `$hidden` attributes.

---

## Installation

### Option A: Full-Stack Laravel Project (Blade + Auth)
For projects like `mofa-education` where Laravel handles both frontend and backend.

#### 1. Add the Package
**Local Development:**
```json
// composer.json
"repositories": [
    { "type": "path", "url": "./Packages/logtracker" }
]
```
```bash
composer require obd/logtracker:dev-dev
```

**Production (via GitHub/Packagist):**
```bash
composer require obd/logtracker
```

#### 2. Publish & Migrate
```bash
php artisan vendor:publish --tag=logtracker-config
php artisan vendor:publish --tag=logtracker-assets --force
php artisan migrate
```

#### 3. Add Trait to Models
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

#### 4. Configure `.env`
```bash
LOGTRACKER_ALLOWED_IDS=1,2,5
```

#### 5. Access
Visit `/audit-panel` in your browser. Done!

---

### Option B: API-Only Laravel Project (Sanctum / No Blade Auth)
For projects like `e-ticketing-api` where Laravel serves only APIs and has no web login route.

#### 1. Add the Package
```json
// composer.json
"repositories": [
    { "type": "path", "url": "../Packages/logtracker" }
],
"minimum-stability": "dev",
"prefer-stable": true
```
```bash
composer require obd/logtracker:dev-dev
```

#### 2. Publish & Migrate
```bash
php artisan vendor:publish --tag=logtracker-config
php artisan vendor:publish --tag=logtracker-assets --force
php artisan migrate
```

#### 3. Configure Middleware & Security (Critical!)
Open `config/obd_tracker.php` and configure for API use:
```php
'ui_middleware'  => ['web'],        // Remove 'auth' to prevent 'login route not found'
'api_middleware' => ['web'],
'access_secret'  => env('LOGTRACKER_ACCESS_SECRET'), // Use this to secure the panel
```

#### 4. Add Trait & Configure .env
```bash
php artisan logtracker:install-trait
```
```bash
# .env 
LOGTRACKER_ACCESS_SECRET=your-secure-random-key
```

#### 5. Access
Visit `http://your-api.test/audit-panel` and enter your secret key, or go directly to `http://your-api.test/audit-panel?secret=your-secure-random-key`.

---

### Option C: Standalone / Headless Mode
Host the Logtracker UI on a completely separate domain (e.g., `audit.example.com`) and connect it to any Laravel backend.

#### 1. Install the package on your Laravel backend (Option A or B above).

#### 2. Export Assets
Copy the `dist/` folder from the package to your static server or CDN.

#### 3. Create a Mount Page
```html
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/path/to/app.css">
</head>
<body>
    <div id="logtracker-root"></div>
    <script>
        window.LOGTRACKER_API_URL = "https://api.yourbackend.com/audit-panel/ui-config";
    </script>
    <script src="/path/to/app.js"></script>
</body>
</html>
```

#### 4. Enable CORS
Ensure your Laravel `config/cors.php` allows requests from the standalone UI domain.

---

## Configuration (`config/obd_tracker.php`)

| Key | Default | Description |
| :--- | :--- | :--- |
| `route_prefix` | `audit-panel` | URL prefix for the UI panel |
| `api_prefix` | `api/audit-panel-data` | URL prefix for the data API |
| `ui_middleware` | `['web', 'auth']` | Middleware for UI routes |
| `api_middleware` | `['web', 'auth']` | Middleware for API routes |
| `allowed_user_ids` | `[]` | Comma-separated User IDs allowed to access the panel (empty = all) |
| `queue_enabled` | `false` | Dispatch logging to a background queue |
| `queue_name` | `default` | Queue connection name |
| `access_secret` | `null` | Secret key for non-auth projects (prevents public access) |
| `retention_days` | `90` | Days to keep logs before pruning |

### Environment Variables
```bash
# Authorization
LOGTRACKER_ALLOWED_IDS=1,2,5
LOGTRACKER_ACCESS_SECRET=your-secret-key-gate

# Performance
LOGTRACKER_QUEUE_ENABLED=true

# MongoDB Sync
LOGTRACKER_MONGO_ENABLED=true
LOGTRACKER_MONGO_CONNECTION=mongodb

# Data Retention
LOGTRACKER_RETENTION_DAYS=90
```

---

## Commands

| Command | Description |
| :--- | :--- |
| `logtracker:install-trait` | Automatically add audit trait to all Eloquent models |
| `logtracker:install-trait --dry-run` | Preview which models would be updated |
| `logtracker:install-trait --dir=app/Models` | Specify a custom scan directory |
| `logtracker:prune` | Remove logs older than retention period |
| `logtracker:prune --days=30` | Override default retention |
| `logtracker:sync-mongo` | Mirror logs to MongoDB |

### Automated Maintenance
```php
$schedule->command('logtracker:prune')->daily();
```

---

## Updating
When updating to a new version, always run:
```bash
composer update obd/logtracker
php artisan vendor:publish --tag=logtracker-assets --force
php artisan optimize:clear
```

---

## Security

- **Path Traversal Protection**: System log viewer uses `basename()` + `realpath()` validation to prevent directory traversal attacks.
- **Extension Enforcement**: Only `.log` files can be accessed through the system log viewer.
- **User Whitelisting**: Access can be restricted to specific user IDs via environment configuration.
- **Privacy**: Model `$hidden` attributes (e.g., passwords) are automatically excluded from audit logs.
- **CSRF Protection**: All POST routes (clear/delete) are protected by Laravel's CSRF middleware.

---
*© 2026 Logtracker Audit System - Premium Enterprise Edition*
