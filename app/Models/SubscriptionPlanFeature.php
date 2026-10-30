<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlanFeature extends Model
{
    protected $table = 'subscription_plan_features';
    protected $fillable = ['plan_id', 'feature_id',
    ];
    public function plan() { return $this->belongsTo(SubscriptionPlan::class, 'plan_id'); }
    public function feature() { return $this->belongsTo(PlatformFeature::class, 'feature_id'); }
}
