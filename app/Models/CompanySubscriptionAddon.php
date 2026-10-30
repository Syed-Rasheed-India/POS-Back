<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySubscriptionAddon extends Model
{
    protected $table = 'company_subscription_addons';

    protected $fillable = [
        'company_subscription_id',
        'addon_id',
        'quantity',
        'unit_price',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function subscription() { return $this->belongsTo(CompanySubscription::class, 'company_subscription_id'); }
    public function addon() { return $this->belongsTo(SubscriptionAddon::class, 'addon_id'); }
}
