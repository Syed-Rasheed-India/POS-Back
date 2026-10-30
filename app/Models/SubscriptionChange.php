<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionChange extends Model
{
    public $timestamps = false;
    protected $table = 'subscription_changes';

    protected $fillable = [
        'company_id',
        'old_subscription_id',
        'new_subscription_id',
        'change_type',
        'old_plan_id',
        'new_plan_id',
        'reason',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function oldSubscription() { return $this->belongsTo(CompanySubscription::class, 'old_subscription_id'); }
    public function newSubscription() { return $this->belongsTo(CompanySubscription::class, 'new_subscription_id'); }
    public function oldPlan() { return $this->belongsTo(SubscriptionPlan::class, 'old_plan_id'); }
    public function newPlan() { return $this->belongsTo(SubscriptionPlan::class, 'new_plan_id'); }
    public function changedBy() { return $this->belongsTo(PlatformAdmin::class, 'changed_by'); }
}
