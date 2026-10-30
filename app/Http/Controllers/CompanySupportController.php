<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanySupportController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        abort_unless($companyId, 403, 'The authenticated user is not linked to a company.');

        $validated = $request->validate([
            'search' => 'nullable|string|max:200',
            'status' => ['nullable', Rule::in(['OPEN','ASSIGNED','IN_PROGRESS','WAITING_FOR_CUSTOMER','WAITING_FOR_SUPPORT','RESOLVED','CLOSED','REOPENED'])],
            'priority' => ['nullable', Rule::in(['LOW','NORMAL','HIGH','CRITICAL'])],
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $tickets = SupportTicket::where('company_id', $companyId)
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(fn ($q) => $q->where('ticket_number', 'ILIKE', "%{$search}%")
                    ->orWhere('subject', 'ILIKE', "%{$search}%"));
            })
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['priority'] ?? null, fn ($q, $priority) => $q->where('priority', $priority))
            ->orderByDesc('updated_at')
            ->paginate($validated['per_page'] ?? 15);

        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->company_id;
        abort_unless($companyId, 403, 'The authenticated user is not linked to a company.');

        $validated = $request->validate([
            'subject' => 'required|string|max:200',
            'description' => 'required|string|max:20000',
            'category' => 'required|string|max:50',
            'priority' => ['required', Rule::in(['LOW', 'NORMAL', 'HIGH', 'CRITICAL'])],
            'related_module' => 'nullable|string|max:50',
            'related_reference' => 'nullable|string|max:100',
        ]);

        $ticket = SupportTicket::create([
            'company_id' => $companyId,
            'company_user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'related_module' => $validated['related_module'] ?? null,
            'related_reference' => $validated['related_reference'] ?? null,
            'status' => 'OPEN',
            'source' => 'WEB',
            'last_message_at' => now(),
        ]);

        $this->audit($request, 'SUPPORT_TICKET_CREATED', 'support_tickets', $ticket->id, ['ticket_uuid' => $ticket->uuid]);
        return response()->json($ticket, 201);
    }

    public function show(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;
        abort_unless($companyId, 403, 'The authenticated user is not linked to a company.');

        $ticket = SupportTicket::where('company_id', $companyId)->where('uuid', $uuid)
            ->with([
                'messages' => fn ($q) => $q->where('is_internal_note', false)
                    ->with(['companyUser:id,first_name,last_name', 'platformAdmin:id,first_name,last_name', 'attachments' => fn ($a) => $a->where('scan_status', 'CLEAN')])
                    ->orderBy('created_at'),
                'attachments' => fn ($q) => $q->whereNull('message_id')->where('scan_status', 'CLEAN'),
            ])->firstOrFail();

        return response()->json($ticket);
    }

    public function reply(Request $request, string $uuid)
    {
        $ticket = SupportTicket::where('company_id', $request->user()->company_id)->where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate(['message_body' => 'required|string|max:20000']);

        $message = SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'COMPANY_USER',
            'company_user_id' => $request->user()->id,
            'message_body' => $validated['message_body'],
            'is_internal_note' => false,
            'message_type' => 'REPLY',
        ]);

        if (in_array($ticket->status, ['WAITING_FOR_CUSTOMER', 'WAITING_FOR_SUPPORT', 'RESOLVED', 'CLOSED'], true)) {
            $ticket->update(['status' => 'IN_PROGRESS', 'resolved_at' => null, 'closed_at' => null]);
        }
        $this->audit($request, 'SUPPORT_TICKET_REPLIED', 'support_tickets', $ticket->id, ['ticket_uuid' => $ticket->uuid]);
        return response()->json($message->load('attachments'), 201);
    }

    public function uploadAttachment(Request $request, string $uuid)
    {
        $ticket = SupportTicket::where('company_id', $request->user()->company_id)->where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate([
            'attachment' => 'required|file|max:10240|mimes:png,jpg,jpeg,pdf,txt',
            'message_uuid' => 'nullable|uuid',
        ]);

        $file = $request->file('attachment');
        $mime = strtolower((string) $file->getMimeType());
        $allowed = ['image/png', 'image/jpeg', 'application/pdf', 'text/plain'];
        abort_unless(in_array($mime, $allowed, true), 422, 'The uploaded file content type is not allowed.');

        $messageId = null;
        if (!empty($validated['message_uuid'])) {
            $message = SupportTicketMessage::where('ticket_id', $ticket->id)
                ->where('uuid', $validated['message_uuid'])->where('is_internal_note', false)->firstOrFail();
            $messageId = $message->id;
        }

        $key = $file->store('support-tickets/'.$ticket->uuid, 'local');
        $attachment = SupportTicketAttachment::create([
            'ticket_id' => $ticket->id,
            'message_id' => $messageId,
            'uploaded_by_type' => 'COMPANY_USER',
            'company_user_id' => $request->user()->id,
            'original_filename' => Str::limit(basename($file->getClientOriginalName()), 255, ''),
            'storage_key' => $key,
            'mime_type' => $mime,
            'file_extension' => strtolower($file->getClientOriginalExtension()),
            'file_size_bytes' => $file->getSize(),
            'checksum_sha256' => hash_file('sha256', $file->getRealPath()),
            // No antivirus scanner is configured in this project; do not mark uploads as clean.
            'scan_status' => 'PENDING',
        ]);

        $this->audit($request, 'SUPPORT_ATTACHMENT_UPLOADED', 'support_ticket_attachments', $attachment->id, ['ticket_uuid' => $ticket->uuid]);
        return response()->json(['message' => 'Attachment uploaded and queued for security scanning.', 'attachment' => $attachment], 201);
    }

    public function reopen(Request $request, string $uuid)
    {
        $ticket = SupportTicket::where('company_id', $request->user()->company_id)->where('uuid', $uuid)->firstOrFail();
        abort_unless(in_array($ticket->status, ['RESOLVED', 'CLOSED'], true), 422, 'Only resolved or closed tickets can be reopened.');
        $ticket->update(['status' => 'REOPENED', 'resolved_at' => null, 'closed_at' => null, 'resolution_summary' => null]);
        $this->audit($request, 'SUPPORT_TICKET_REOPENED', 'support_tickets', $ticket->id, ['ticket_uuid' => $ticket->uuid]);
        return response()->json(['message' => 'Ticket reopened successfully.', 'ticket' => $ticket->fresh()]);
    }

    public function downloadAttachment(Request $request, string $uuid, string $attachmentUuid)
    {
        $ticket = SupportTicket::where('company_id', $request->user()->company_id)->where('uuid', $uuid)->firstOrFail();
        $attachment = SupportTicketAttachment::where('ticket_id', $ticket->id)->where('uuid', $attachmentUuid)->firstOrFail();
        abort_unless($attachment->scan_status === 'CLEAN', 423, 'This attachment is not available until security scanning marks it clean.');
        abort_unless(Storage::disk('local')->exists($attachment->storage_key), 404, 'Attachment file not found.');
        return Storage::disk('local')->download($attachment->storage_key, $attachment->original_filename, ['Content-Type' => $attachment->mime_type, 'X-Content-Type-Options' => 'nosniff']);
    }

    public function submitRating(Request $request, string $uuid)
    {
        $ticket = SupportTicket::where('company_id', $request->user()->company_id)->where('uuid', $uuid)->firstOrFail();
        abort_unless(in_array($ticket->status, ['RESOLVED', 'CLOSED'], true), 422, 'Feedback can be submitted after a ticket is resolved.');
        $validated = $request->validate([
            'satisfaction_rating' => 'required|integer|min:1|max:5',
            'satisfaction_feedback' => 'nullable|string|max:5000',
        ]);
        $ticket->update($validated);
        $this->audit($request, 'SUPPORT_TICKET_RATED', 'support_tickets', $ticket->id, ['rating' => $validated['satisfaction_rating']]);
        return response()->json(['message' => 'Feedback submitted successfully.', 'ticket' => $ticket->fresh()]);
    }

    private function audit(Request $request, string $action, string $entity, int $entityId, array $metadata = []): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('platform_audit_logs')) return;
        $requestId = $request->header('X-Request-ID');
        DB::table('platform_audit_logs')->insert([
            'uuid' => (string) Str::uuid(), 'actor_type' => 'COMPANY_USER',
            'company_user_id' => $request->user()->id, 'company_id' => $request->user()->company_id,
            'action_code' => $action, 'entity_type' => $entity, 'entity_id' => $entityId,
            'entity_uuid' => isset($metadata['ticket_uuid']) && Str::isUuid((string) $metadata['ticket_uuid']) ? $metadata['ticket_uuid'] : null,
            'description' => $action, 'new_values' => json_encode($metadata),
            'request_id' => $requestId && Str::isUuid($requestId) ? $requestId : null,
            'source_ip' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 1000), 'created_at' => now(),
        ]);
    }
}
