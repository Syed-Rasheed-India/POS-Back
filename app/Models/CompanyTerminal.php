<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyTerminal extends Model
{
    protected $table = 'company_terminals';

    protected $fillable = [
        'uuid',
        'company_id',
        'branch_id',
        'terminal_code',
        'terminal_name',
        'device_id',
        'status',
    ];
}