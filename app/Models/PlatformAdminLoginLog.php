<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformAdminLoginLog extends Model
{
    protected $table = 'platform_admin_login_logs';

    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'username',
        'login_status',
        'ip_address',
        'user_agent',
        'failure_reason',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];
}