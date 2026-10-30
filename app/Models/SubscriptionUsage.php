<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionUsage extends Model
{
    protected $table = 'subscription_usage';

    protected $fillable = [
        'company_id',
        'subscription_id',
        'usage_code',
        'usage_value',
        'last_calculated_at',
    ];

    protected $casts = [
        'usage_value' => 'decimal:4',
        'last_calculated_at' => 'datetime',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function subscription() { return $this->belongsTo(CompanySubscription::class, 'subscription_id'); }
}
