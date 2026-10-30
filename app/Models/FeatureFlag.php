<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $table = 'feature_flags';

    protected $fillable = [
        'feature_id',
        'flag_key',
        'is_enabled',
        'rollout_percentage',
        'conditions',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'rollout_percentage' => 'decimal:4',
        'conditions' => 'array',
    ];

    public function feature()
    {
        return $this->belongsTo(
            PlatformFeature::class,
            'feature_id'
        );
    }
}