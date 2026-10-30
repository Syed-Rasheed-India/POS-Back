<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformAdminSession extends Model
{
    protected $table = 'platform_admin_sessions';

    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'admin_id',
        'token_hash',
        'ip_address',
        'user_agent',
        'device_info',
        'expires_at',
        'last_activity_at',
        'revoked_at',
        'created_at',
    ];

    protected $casts = [
        'device_info' => 'array',
        'expires_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}