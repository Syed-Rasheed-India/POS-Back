<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDevice extends Model
{
    protected $table = 'company_devices';

    protected $fillable = [
        'uuid',
        'company_id',
        'branch_id',
        'device_code',
        'device_name',
        'device_type',
        'platform',
        'app_version',
        'last_seen_at',
        'status',
        'device_metadata',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'device_metadata' => 'array',
    ];
}