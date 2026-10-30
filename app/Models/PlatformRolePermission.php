<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformRolePermission extends Model
{
    protected $table = 'platform_role_permissions';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'permission_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}