<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionTrial extends Model
{
    public $timestamps = false;
    protected $table = 'subscription_trials';

    protected $fillable = [
        'company_id',
        'subscription_id',
        'start_date',
        'end_date',
        'status',
        'converted_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'converted_at' => 'datetime',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function subscription() { return $this->belongsTo(CompanySubscription::class, 'subscription_id'); }
}
