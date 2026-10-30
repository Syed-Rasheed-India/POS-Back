<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorRole extends Model
{
    protected $table = 'vendor_roles';

    protected $fillable = [
        'company_id',
        'role_code',
        'role_name',
        'description',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            VendorPermission::class,
            'vendor_role_permissions',
            'role_id',
            'permission_id'
        )->withTimestamps();
    }

    public function userAssignments(): HasMany
    {
        return $this->hasMany(VendorUserRole::class, 'role_id');
    }
}
