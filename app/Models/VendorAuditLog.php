<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorAuditLog extends Model
{
    protected $table = 'vendor_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'actor_user_id',
        'action',
        'entity_type',
        'entity_id',
        'details',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(CompanyUser::class, 'actor_user_id');
    }
}
