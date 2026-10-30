<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyLimitOverride extends Model
{
    protected $table = 'company_limit_overrides';

    protected $fillable = [
        'uuid',
        'company_id',
        'subscription_id',
        'limit_code',
        'base_limit',
        'override_value',
        'override_type',
        'reason',
        'starts_at',
        'expires_at',
        'status',
        'created_by',
    ];

    protected $casts = [
        'base_limit' => 'decimal:4',
        'override_value' => 'decimal:4',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function subscription() { return $this->belongsTo(CompanySubscription::class, 'subscription_id'); }
    public function createdBy() { return $this->belongsTo(PlatformAdmin::class, 'created_by'); }
}
