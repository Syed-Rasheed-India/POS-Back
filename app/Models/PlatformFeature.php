<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformFeature extends Model
{
    protected $table = 'platform_features';

    protected $fillable = [
        'module_id',
        'feature_code',
        'feature_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(
            PlatformModule::class,
            'module_id'
        );
    }

    public function featureFlag()
    {
        return $this->hasOne(
            FeatureFlag::class,
            'feature_id'
        );
    }
}