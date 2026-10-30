<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformConfig extends Model
{
    protected $table = 'platform_configs';

    protected $fillable = [
        'config_group',
        'config_key',
        'config_value',
        'environment',
        'is_encrypted',
    ];

    protected $casts = [
        'config_value' => 'array',
        'is_encrypted' => 'boolean',
    ];
}