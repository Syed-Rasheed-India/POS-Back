<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionGracePeriod extends Model
{
    public $timestamps = false;
    protected $table = 'subscription_grace_periods';

    protected $fillable = [
        'company_id',
        'subscription_id',
        'start_date',
        'end_date',
        'reason',
        'approved_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function subscription() { return $this->belongsTo(CompanySubscription::class, 'subscription_id'); }
    public function approver() { return $this->belongsTo(PlatformAdmin::class, 'approved_by'); }
}
