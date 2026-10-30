<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
{
    protected $table = 'company_addresses';

    protected $fillable = [
        'company_id',
        'address_type',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country_id',
        'latitude',
        'longitude',
        'is_primary',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_primary' => 'boolean',
    ];
}