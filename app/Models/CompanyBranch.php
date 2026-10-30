<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyBranch extends Model
{
    use SoftDeletes;

    protected $table = 'company_branches';

    protected $fillable = [
        'uuid',
        'company_id',
        'branch_code',
        'branch_name',
        'address_id',
        'status',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}