<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionUsageLog extends Model
{
    public $timestamps = false;
    protected $table = 'subscription_usage_logs';

    protected $fillable = [
        'company_id',
        'subscription_id',
        'usage_code',
        'previous_value',
        'new_value',
        'source',
    ];

    protected $casts = [
        'previous_value' => 'decimal:4',
        'new_value' => 'decimal:4',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function subscription() { return $this->belongsTo(CompanySubscription::class, 'subscription_id'); }
}
