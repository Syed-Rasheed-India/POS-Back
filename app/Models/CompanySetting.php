<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $table = 'company_settings';

    protected $fillable = [
        'company_id',
        'setting_key',
        'setting_value',
    ];

    protected $casts = [
        'setting_value' => 'array',
    ];
}