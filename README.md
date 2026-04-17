# Logtracker

A comprehensive Laravel package for tracking and auditing user activities and system events in your application.

## Features

- User activity tracking and logging
- Audit trail management
- API token verification for secure logging
- Easy integration with Laravel applications
- Blade template audit panel for viewing logs
- Service provider for seamless setup

## Requirements

- PHP 7.4 or higher
- Laravel 8.0 or higher
- Composer

## Installation

Install the package via Composer:

```bash
composer require obd/logtracker
```

## Configuration

After installation, the package will be auto-discovered by Laravel. You can publish the configuration file:

```bash
php artisan vendor:publish --provider="Obd\Logtracker\LogtrackerServiceProvider"
```

This will publish the configuration file to `config/obd_tracker.php` where you can customize the package settings.

## Usage

### Basic Setup

Add the `Logtrackerable` trait to your models:

```php
use Obd\Logtracker\Traits\Logtrackerable;

class User extends Model
{
    use Logtrackerable;
    // ...
}
```

### Viewing the Audit Panel

Access the audit panel through the provided route:

```
/audit-log
```

### API Integration

Secure your logging endpoints with the `VerifyLogApiToken` middleware:

```php
Route::post('/log/event', 'LogtrackerController@store')
    ->middleware('verify.log.api.token');
```

## Database Migration

Run the migrations to create the necessary database tables:

```bash
php artisan migrate
```

## Events

The package includes event listeners for tracking user logins:

- `LoginListener` - Automatically logs user login events

## API Endpoints

The `LogtrackerController` provides RESTful endpoints for managing logs.

## Configuration Options

Customize the package behavior in `config/obd_tracker.php`:

- API token verification settings
- Log retention policies
- Tracked events configuration

## License

This package is proprietary software. All rights reserved.

## Support

For issues, feature requests, or contributions, please contact the development team.
