<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformAdminUserRole extends Model
{
    protected $table = 'platform_admin_user_roles';

    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'role_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}