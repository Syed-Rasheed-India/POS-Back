<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorUserRole extends Model
{
    protected $table = 'vendor_user_roles';

    protected $fillable = [
        'company_id',
        'company_user_id',
        'role_id',
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

    public function role(): BelongsTo
    {
        return $this->belongsTo(VendorRole::class, 'role_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(CompanyUser::class, 'assigned_by');
    }
}
