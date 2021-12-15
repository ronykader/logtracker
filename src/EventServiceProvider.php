<?php

namespace Obd\Logtracker;

use Illuminate\Auth\Events\Login;
use Obd\Logtracker\Listeners\LoginListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;


class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class   => [
            LoginListener::class
        ]
    ];

    public function boot()
    {
        parent::boot();
    }
}