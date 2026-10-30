<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsernameRule extends Model
{
    protected $table = 'username_rules';

    protected $fillable = [
        'company_id',
        'min_length',
        'max_length',
        'allow_numbers',
        'allow_special_characters',
        'require_unique',
        'pattern',
    ];

    protected $casts = [
        'allow_numbers' => 'boolean',
        'allow_special_characters' => 'boolean',
        'require_unique' => 'boolean',
    ];
}