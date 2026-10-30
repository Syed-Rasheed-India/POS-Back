<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformPermission extends Model
{
    protected $table = 'platform_permissions';

    public $timestamps = false;

    protected $fillable = [
        'permission_code',
        'permission_name',
        'module',
        'action',
        'description',
    ];
}