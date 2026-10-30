<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlanModule extends Model
{
    protected $table = 'subscription_plan_modules';
    protected $fillable = ['plan_id', 'module_id',
    ];
    public function plan() { return $this->belongsTo(SubscriptionPlan::class, 'plan_id'); }
    public function module() { return $this->belongsTo(PlatformModule::class, 'module_id'); }
}
