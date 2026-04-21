<?php

namespace Obd\Logtracker\Listeners;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class LoginListener
{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(Login $event)
    {
        $user     = $event->user;
        $dateTime = date('Y-m-d H:i:s');

        // Resolve User Metadata (designation, office, etc.)
        $user_array = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email ?? '',
        ];
        
        $optionalFields = ['designation', 'officeNameEng', 'officeNameBng'];
        foreach ($optionalFields as $field) {
            if (isset($user->$field)) {
                $user_array[$field] = $user->$field;
            }
        }
        
        $userInfo = json_encode($user_array);

        // Capture Request Metadata
        $logData = [
            'users'       => $userInfo,
            'user_id'     => $user->id,
            'log_date'    => $dateTime,
            'table_name'  => 'users',
            'log_type'    => 'login',
            'data'        => json_encode([
                'ip_address' => $this->request->ip(),
                'user_agent' => $this->request->userAgent()
            ]),
            'ip_address'  => $this->request->ip(),
            'user_agent'  => $this->request->userAgent(),
            'url'         => $this->request->fullUrl(),
            'route_name'  => $this->request->route() ? $this->request->route()->getName() : null,
            'synchronous' => 0,
        ];

        DB::table('logtrackers')->insert($logData);
    }
}