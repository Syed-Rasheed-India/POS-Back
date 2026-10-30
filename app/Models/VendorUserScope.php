<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorUserScope extends Model
{
    protected $table = 'vendor_user_scopes';

    protected $fillable = [
        'company_id',
        'company_user_id',
        'scope_type',
        'scope_id',
        'assigned_by',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(CompanyUser::class, 'company_user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(CompanyUser::class, 'assigned_by');
    }
}
