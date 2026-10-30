<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SupportTicketAttachment extends Model {
    use SoftDeletes;

    public $timestamps = false;

    protected $hidden = ['storage_key', 'checksum_sha256'];

    protected $fillable = [
        'uuid', 'ticket_id', 'message_id', 'uploaded_by_type', 'company_user_id',
        'platform_admin_id', 'original_filename', 'storage_key', 'mime_type',
        'file_extension', 'file_size_bytes', 'checksum_sha256', 'scan_status', 'created_at'
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($attachment) {
            if (empty($attachment->uuid)) {
                $attachment->uuid = (string) Str::uuid();
            }
            if (empty($attachment->created_at)) {
                $attachment->created_at = now();
            }
        });
    }

    public function ticket(): BelongsTo {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function message(): BelongsTo {
        return $this->belongsTo(SupportTicketMessage::class, 'message_id');
    }
}
