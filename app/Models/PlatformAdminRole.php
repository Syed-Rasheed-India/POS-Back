<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformAdminRole extends Model
{
    protected $table = 'platform_admin_roles';

    protected $fillable = [
        'uuid',
        'role_code',
        'role_name',
        'description',
        'is_system_role',
        'is_active',
    ];

    protected $casts = [
        'is_system_role' => 'boolean',
        'is_active' => 'boolean',
    ];
}