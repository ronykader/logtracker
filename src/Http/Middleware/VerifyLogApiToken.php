<?php

namespace Obd\Logtracker\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyLogApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if( $request->header('Authorization') !== config('logtracker.service_audit') ) {
            return response()->json(['error-message' => 'Token mismatch with service audit token'],404);
        }
        return $next($request);
    }
}
