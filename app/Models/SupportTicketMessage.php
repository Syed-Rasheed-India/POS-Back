<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicketMessage extends Model {
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'ticket_id', 'sender_type', 'company_user_id', 'platform_admin_id',
        'message_type', 'message_body', 'is_internal_note'
    ];

    protected $casts = [
        'is_internal_note' => 'boolean',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($message) {
            if (empty($message->uuid)) {
                $message->uuid = (string) Str::uuid();
            }
        });

        static::created(function ($message) {
            $message->ticket()->update(['last_message_at' => now()]);
        });
    }

    public function ticket(): BelongsTo {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function companyUser(): BelongsTo {
        return $this->belongsTo(CompanyUser::class);
    }

    public function platformAdmin(): BelongsTo {
        return $this->belongsTo(PlatformAdmin::class);
    }

    public function attachments(): HasMany {
        return $this->hasMany(SupportTicketAttachment::class, 'message_id');
    }
}
