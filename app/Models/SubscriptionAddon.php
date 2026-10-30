<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionAddon extends Model
{
    protected $table = 'subscription_addons';

    protected $fillable = [
        'uuid',
        'addon_code',
        'addon_name',
        'description',
        'price',
        'currency_id',
        'billing_cycle',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function currency() { return $this->belongsTo(Currency::class); }
    public function companySubscriptions() { return $this->hasMany(CompanySubscriptionAddon::class, 'addon_id'); }
}
