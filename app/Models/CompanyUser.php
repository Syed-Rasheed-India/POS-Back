<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class CompanyUser extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
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

    protected $hidden = [
        'password_hash',
        'remember_token',
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

    protected static function booted(): void
    {
        static::creating(function (CompanyUser $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
