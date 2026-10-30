<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyWarehouse extends Model
{
    use SoftDeletes;

    protected $table = 'company_warehouses';

    protected $fillable = [
        'uuid',
        'company_id',
        'branch_id',
        'warehouse_code',
        'warehouse_name',
        'status',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}