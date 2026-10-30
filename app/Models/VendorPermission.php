<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VendorPermission extends Model
{
    protected $table = 'vendor_permissions';

    protected $fillable = [
        'permission_code',
        'permission_name',
        'module',
        'action',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            VendorRole::class,
            'vendor_role_permissions',
            'permission_id',
            'role_id'
        )->withTimestamps();
    }
}
