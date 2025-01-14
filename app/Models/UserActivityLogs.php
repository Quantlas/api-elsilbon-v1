<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLogs extends Model
{
    use HasFactory;

    protected $fillable = [
        'user',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'referrer',
        'severity',
    ];
}
