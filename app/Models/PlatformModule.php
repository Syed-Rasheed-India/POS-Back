<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformModule extends Model
{
    protected $table = 'platform_modules';

    protected $fillable = [
        'module_code',
        'module_name',
        'description',
        'version',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function features()
    {
        return $this->hasMany(
            PlatformFeature::class,
            'module_id'
        );
    }
}