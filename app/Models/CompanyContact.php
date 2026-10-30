<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyContact extends Model
{
    protected $table = 'company_contacts';

    protected $fillable = [
        'uuid',
        'company_id',
        'contact_type',
        'contact_name',
        'email',
        'phone',
        'designation',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];
}