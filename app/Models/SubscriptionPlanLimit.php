<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlanLimit extends Model
{
    protected $table = 'subscription_plan_limits';
    protected $fillable = ['plan_id', 'limit_code', 'limit_value', 'unlimited',
    ];
    protected $casts = [
        'limit_value' => 'decimal:4', 'unlimited' => 'boolean',
    ];
    public function plan() { return $this->belongsTo(SubscriptionPlan::class, 'plan_id'); }
}
