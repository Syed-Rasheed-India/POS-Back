<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $table = 'subscription_plans';

    protected $fillable = [
        'uuid', 'plan_code', 'plan_name', 'description',
        'monthly_price', 'quarterly_price', 'half_yearly_price', 'yearly_price',
        'currency_id', 'trial_days', 'grace_days', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'quarterly_price' => 'decimal:2',
        'half_yearly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'trial_days' => 'integer',
        'grace_days' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function currency() { return $this->belongsTo(Currency::class); }
    public function subscriptions() { return $this->hasMany(CompanySubscription::class, 'plan_id'); }
    public function limits() { return $this->hasMany(SubscriptionPlanLimit::class, 'plan_id'); }
    public function planModules() { return $this->hasMany(SubscriptionPlanModule::class, 'plan_id'); }
    public function planFeatures() { return $this->hasMany(SubscriptionPlanFeature::class, 'plan_id'); }
    public function modules() { return $this->belongsToMany(PlatformModule::class, 'subscription_plan_modules', 'plan_id', 'module_id'); }
    public function features() { return $this->belongsToMany(PlatformFeature::class, 'subscription_plan_features', 'plan_id', 'feature_id'); }
}
