<?php

namespace App\Services;

use App\Models\UserActivityLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserActivityService
{
    static function log($user, $action, $description = null, $severity = "info")
    {
        UserActivityLogs::create([
            'user' => $user,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->getClientIp(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->fullUrl(),
            'severity' => $severity
        ]);
    }

    static function ping(Request $request)
    {
        UserActivityLogs::create([
            'user' => 'guest',
            'action' => 'access-home',
            'description' => 'Accedió a la página',
            'ip_address' => request()->getClientIp(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->fullUrl(),
            'severity' => 'info'
        ]);
    }
}
