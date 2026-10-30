<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicket extends Model {
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'ticket_number', 'company_id', 'company_user_id', 'assigned_admin_id',
        'subject', 'description', 'category', 'priority', 'status', 'source',
        'related_module', 'related_reference', 'first_response_at', 'resolved_at',
        'closed_at', 'resolution_summary', 'satisfaction_rating', 'satisfaction_feedback',
        'last_message_at'
    ];

    protected $casts = [
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_message_at' => 'datetime',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($ticket) {
            if (empty($ticket->uuid)) {
                $ticket->uuid = (string) Str::uuid();
            }
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'SUP-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function company(): BelongsTo {
        return $this->belongsTo(Company::class);
    }

    public function companyUser(): BelongsTo {
        return $this->belongsTo(CompanyUser::class);
    }

    public function assignedAdmin(): BelongsTo {
        return $this->belongsTo(PlatformAdmin::class, 'assigned_admin_id');
    }

    public function messages(): HasMany {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id');
    }

    public function attachments(): HasMany {
        return $this->hasMany(SupportTicketAttachment::class, 'ticket_id');
    }
}
