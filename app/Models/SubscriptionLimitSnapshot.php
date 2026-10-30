<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionLimitSnapshot extends Model
{
    public $timestamps = false;
    protected $table = 'subscription_limit_snapshots';

    protected $fillable = [
        'subscription_id',
        'limit_code',
        'limit_value',
        'unlimited',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'limit_value' => 'decimal:4',
        'unlimited' => 'boolean',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
    ];

    public function subscription() { return $this->belongsTo(CompanySubscription::class, 'subscription_id'); }
}
