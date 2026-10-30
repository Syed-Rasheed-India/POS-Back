<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyTaxSetting extends Model
{
    protected $table = 'company_tax_settings';

    protected $fillable = [
        'uuid',
        'company_id',
        'country_id',
        'tax_registration_number',
        'tax_system_name',
        'default_tax_rate',
        'tax_inclusive',
        'tax_enabled',
        'tax_configuration',
        'effective_from',
        'effective_to',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'default_tax_rate' => 'decimal:4',
        'tax_inclusive' => 'boolean',
        'tax_enabled' => 'boolean',
        'tax_configuration' => 'array',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];
}