<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SuperAdminSupportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:200',
            'company_id' => 'nullable|integer|min:1',
            'status' => ['nullable', Rule::in(['OPEN','ASSIGNED','IN_PROGRESS','WAITING_FOR_CUSTOMER','WAITING_FOR_SUPPORT','RESOLVED','CLOSED','REOPENED'])],
            'priority' => ['nullable', Rule::in(['LOW','NORMAL','HIGH','CRITICAL'])],
            'category' => 'nullable|string|max:50', 'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        $tickets = SupportTicket::with(['company', 'assignedAdmin', 'companyUser'])
            ->when($validated['search'] ?? null, fn ($q, $s) => $q->where(fn ($x) => $x->where('ticket_number', 'ILIKE', "%{$s}%")->orWhere('subject', 'ILIKE', "%{$s}%")))
            ->when($validated['company_id'] ?? null, fn ($q, $id) => $q->where('company_id', $id))
            ->when($validated['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($validated['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->when($validated['category'] ?? null, fn ($q, $v) => $q->where('category', $v))
            ->orderByDesc('created_at')->paginate($validated['per_page'] ?? 20);
        return response()->json($tickets);
    }

    public function show(string $uuid)
    {
        $ticket = SupportTicket::where('uuid', $uuid)->with([
            'company', 'companyUser', 'assignedAdmin',
            'messages' => fn ($q) => $q->with(['companyUser', 'platformAdmin', 'attachments'])->orderBy('created_at'),
            'attachments',
        ])->firstOrFail();
        return response()->json($ticket);
    }

    public function assign(Request $request, string $uuid)
    {
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();
        $data = $request->validate(['assigned_admin_id' => 'required|integer|exists:platform_admins,id']);
        $ticket->update(['assigned_admin_id' => $data['assigned_admin_id'], 'status' => $ticket->status === 'OPEN' ? 'ASSIGNED' : $ticket->status]);
        $this->audit($request, 'SUPPORT_TICKET_ASSIGNED', 'support_tickets', $ticket->id, ['assigned_admin_id' => $data['assigned_admin_id']]);
        return response()->json(['message' => 'Ticket assigned successfully.', 'ticket' => $ticket->fresh()]);
    }

    public function updateStatus(Request $request, string $uuid)
    {
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();
        $data = $request->validate([
            'status' => ['required', Rule::in(['OPEN','ASSIGNED','IN_PROGRESS','WAITING_FOR_CUSTOMER','WAITING_FOR_SUPPORT','RESOLVED','CLOSED','REOPENED'])],
            'resolution_summary' => 'nullable|string|max:10000',
        ]);
        $updates = ['status' => $data['status']];
        if ($data['status'] === 'RESOLVED') { $updates['resolved_at'] = now(); $updates['resolution_summary'] = $data['resolution_summary'] ?? null; }
        if ($data['status'] === 'CLOSED') $updates['closed_at'] = now();
        if (in_array($data['status'], ['OPEN','ASSIGNED','IN_PROGRESS','WAITING_FOR_CUSTOMER'], true)) { $updates['resolved_at'] = null; $updates['closed_at'] = null; }
        $old = $ticket->status;
        $ticket->update($updates);
        $this->audit($request, 'SUPPORT_TICKET_STATUS_CHANGED', 'support_tickets', $ticket->id, ['from' => $old, 'to' => $data['status']]);
        return response()->json(['message' => 'Ticket status updated.', 'ticket' => $ticket->fresh()]);
    }

    public function updatePriority(Request $request, string $uuid)
    {
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();
        $data = $request->validate(['priority' => ['required', Rule::in(['LOW','NORMAL','HIGH','CRITICAL'])]]);
        $old = $ticket->priority;
        $ticket->update(['priority' => $data['priority']]);
        $this->audit($request, 'SUPPORT_TICKET_PRIORITY_CHANGED', 'support_tickets', $ticket->id, ['from' => $old, 'to' => $data['priority']]);
        return response()->json(['message' => 'Ticket priority updated.', 'ticket' => $ticket->fresh()]);
    }

    public function reply(Request $request, string $uuid)
    {
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();
        $data = $request->validate(['message_body' => 'required|string|max:20000']);
        $message = SupportTicketMessage::create([
            'ticket_id' => $ticket->id, 'sender_type' => 'PLATFORM_ADMIN', 'platform_admin_id' => $request->user()->id,
            'message_body' => $data['message_body'], 'is_internal_note' => false, 'message_type' => 'REPLY',
        ]);
        $updates = ['status' => 'WAITING_FOR_CUSTOMER'];
        if (!$ticket->first_response_at) $updates['first_response_at'] = now();
        $ticket->update($updates);
        $this->audit($request, 'SUPPORT_TICKET_REPLIED', 'support_tickets', $ticket->id, ['message_uuid' => $message->uuid]);
        return response()->json($message->load('attachments'), 201);
    }

    public function addInternalNote(Request $request, string $uuid)
    {
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();
        $data = $request->validate(['message_body' => 'required|string|max:20000']);
        $message = SupportTicketMessage::create([
            'ticket_id' => $ticket->id, 'sender_type' => 'PLATFORM_ADMIN', 'platform_admin_id' => $request->user()->id,
            'message_type' => 'SYSTEM', 'message_body' => $data['message_body'], 'is_internal_note' => true,
        ]);
        $this->audit($request, 'SUPPORT_TICKET_INTERNAL_NOTE_ADDED', 'support_tickets', $ticket->id, ['message_uuid' => $message->uuid]);
        return response()->json(['message' => 'Internal note added. It is visible only to authorized support staff.', 'note' => $message], 201);
    }

    public function downloadAttachment(string $uuid, string $attachmentUuid)
    {
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();
        $attachment = SupportTicketAttachment::where('ticket_id', $ticket->id)->where('uuid', $attachmentUuid)->firstOrFail();
        abort_unless($attachment->scan_status === 'CLEAN', 423, 'This attachment is not available until security scanning marks it clean.');
        abort_unless(Storage::disk('local')->exists($attachment->storage_key), 404, 'Attachment file not found.');
        return Storage::disk('local')->download($attachment->storage_key, $attachment->original_filename, ['Content-Type' => $attachment->mime_type, 'X-Content-Type-Options' => 'nosniff']);
    }

    public function reports(Request $request)
    {
        $from = $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from']);
        $base = SupportTicket::query()->when($from['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($from['to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
        $tickets = (clone $base)->get(['id','status','priority','assigned_admin_id','created_at','first_response_at','resolved_at','satisfaction_rating']);
        $avgSeconds = static function ($rows, string $end): ?float {
            $values = $rows->filter(fn ($t) => $t->created_at && $t->{$end})
                ->map(fn ($t) => $t->created_at->diffInSeconds($t->{$end}, false))->filter(fn ($v) => $v >= 0);
            return $values->isEmpty() ? null : round($values->avg(), 2);
        };
        return response()->json([
            'total_tickets' => $tickets->count(),
            'open_tickets' => $tickets->whereIn('status', ['OPEN','ASSIGNED','IN_PROGRESS'])->count(),
            'overdue_tickets' => (clone $base)->whereNotIn('status', ['RESOLVED','CLOSED'])->where('created_at', '<', now()->subDays(3))->count(),
            'resolved_tickets' => $tickets->whereIn('status', ['RESOLVED','CLOSED'])->count(),
            'unassigned_tickets' => $tickets->whereNull('assigned_admin_id')->count(),
            'average_first_response_seconds' => $avgSeconds($tickets, 'first_response_at'),
            'average_resolution_seconds' => $avgSeconds($tickets, 'resolved_at'),
            'average_satisfaction_rating' => $tickets->whereNotNull('satisfaction_rating')->avg('satisfaction_rating'),
            'by_status' => (clone $base)->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->orderBy('status')->get(),
            'by_priority' => (clone $base)->select('priority', DB::raw('COUNT(*) as total'))->groupBy('priority')->orderBy('priority')->get(),
            'agent_workload' => (clone $base)->select('assigned_admin_id', DB::raw('COUNT(*) as total'))->groupBy('assigned_admin_id')->orderByDesc('total')->get(),
        ]);
    }

    private function audit(Request $request, string $action, string $entity, int $entityId, array $metadata = []): void
    {
        if (!Schema::hasTable('platform_audit_logs')) return;
        $requestId = $request->header('X-Request-ID');
        DB::table('platform_audit_logs')->insert([
            'uuid' => (string) Str::uuid(), 'actor_type' => 'PLATFORM_ADMIN', 'platform_admin_id' => $request->user()->id,
            'action_code' => $action, 'entity_type' => $entity, 'entity_id' => $entityId,
            'entity_uuid' => isset($metadata['ticket_uuid']) && Str::isUuid((string) $metadata['ticket_uuid']) ? $metadata['ticket_uuid'] : null,
            'description' => $action, 'new_values' => json_encode($metadata),
            'request_id' => $requestId && Str::isUuid($requestId) ? $requestId : null,
            'source_ip' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 1000), 'created_at' => now(),
        ]);
    }
}
