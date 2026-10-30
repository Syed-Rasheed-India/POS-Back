<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    protected $fillable = [
        'country_code',
        'country_name',
        'phone_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}