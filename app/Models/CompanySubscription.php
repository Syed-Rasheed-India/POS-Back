<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySubscription extends Model
{
    protected $table = 'company_subscriptions';

    protected $fillable = [
        'uuid',
        'company_id',
        'plan_id',
        'billing_cycle',
        'start_date',
        'end_date',
        'status',
        'auto_renew',
        'price',
        'currency_id',
    ];

    protected $casts = [
        'auto_renew' => 'boolean',
        'price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function plan() { return $this->belongsTo(SubscriptionPlan::class, 'plan_id'); }
    public function currency() { return $this->belongsTo(Currency::class); }
    public function limitSnapshots() { return $this->hasMany(SubscriptionLimitSnapshot::class, 'subscription_id'); }
    public function usage() { return $this->hasMany(SubscriptionUsage::class, 'subscription_id'); }
    public function usageLogs() { return $this->hasMany(SubscriptionUsageLog::class, 'subscription_id'); }
    public function addons() { return $this->hasMany(CompanySubscriptionAddon::class, 'company_subscription_id'); }
    public function trials() { return $this->hasMany(SubscriptionTrial::class, 'subscription_id'); }
    public function gracePeriods() { return $this->hasMany(SubscriptionGracePeriod::class, 'subscription_id'); }
    public function changesFrom() { return $this->hasMany(SubscriptionChange::class, 'old_subscription_id'); }
    public function changesTo() { return $this->hasMany(SubscriptionChange::class, 'new_subscription_id'); }
    public function limitOverrides() { return $this->hasMany(CompanyLimitOverride::class, 'subscription_id'); }
}
