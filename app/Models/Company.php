<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model {
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'domain', 'status', 'subscription_plan_id'];

    public function users(): HasMany {
        return $this->hasMany(CompanyUser::class);
    }

    public function supportTickets(): HasMany {
        return $this->hasMany(SupportTicket::class);
    }
}
