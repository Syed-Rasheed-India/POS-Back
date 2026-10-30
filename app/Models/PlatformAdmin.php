<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class PlatformAdmin extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'platform_admins';

    protected $hidden = [
        'password',
        'password_hash',
        'remember_token',
        'secret_encrypted',
    ];

    protected $fillable = [
        'uuid',
        'username',
        'email',
        'password_hash',
        'first_name',
        'last_name',
        'phone',
        'status',
        'last_login_at',
        'password_changed_at',
        'failed_login_attempts',
        'locked_until',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'locked_until' => 'datetime',
            'failed_login_attempts' => 'integer',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            PlatformAdminRole::class,
            'platform_admin_user_roles',
            'admin_id',
            'role_id'
        );
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(
            SupportTicket::class,
            'assigned_admin_id'
        );
    }

    public function hasSuperAdminRole(): bool
    {
        return $this->roles()
            ->whereIn('platform_admin_roles.role_code', [
                'SUPER_ADMIN',
                'super_admin',
                'super-admin',
            ])
            ->where('platform_admin_roles.is_active', true)
            ->exists();
    }
}
