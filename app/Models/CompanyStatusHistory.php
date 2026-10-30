<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyStatusHistory extends Model
{
    protected $table = 'company_status_history';

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'old_status',
        'new_status',
        'reason',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];
}