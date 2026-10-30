<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminSupportController extends Controller
{
    /**
     * Get all support tickets with filtering (Phase 12)
     */
    public function index(Request $request)
    {
        $query = DB::table('support_tickets')
            ->leftJoin('companies', 'support_tickets.company_id', '=', 'companies.id')
            ->select('support_tickets.*', 'companies.name as company_name');

        if ($request->has('status')) {
            $query->where('support_tickets.status', $request->input('status'));
        }

        if ($request->has('priority')) {
            $query->where('support_tickets.priority', $request->input('priority'));
        }

        $tickets = $query->orderBy('support_tickets.created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * View single ticket details and conversation messages
     */
    public function show($uuid)
    {
        $ticket = DB::table('support_tickets')->where('uuid', $uuid)->first();

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket not found'], 404);
        }

        $messages = DB::table('support_ticket_messages')
            ->where('ticket_id', $ticket->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => $ticket,
                'messages' => $messages
            ]
        ]);
    }

    /**
     * Assign support agent to a ticket
     */
    public function assign(Request $request, $uuid)
    {
        $request->validate(['admin_id' => 'required|integer']);

        DB::table('support_tickets')
            ->where('uuid', $uuid)
            ->update([
                'assigned_admin_id' => $request->input('admin_id'),
                'status' => 'ASSIGNED',
                'updated_at' => now()
            ]);

        return response()->json(['success' => true, 'message' => 'Ticket assigned successfully.']);
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, $uuid)
    {
        $request->validate(['status' => 'required|string']);

        DB::table('support_tickets')
            ->where('uuid', $uuid)
            ->update([
                'status' => $request->input('status'),
                'updated_at' => now()
            ]);

        return response()->json(['success' => true, 'message' => 'Ticket status updated successfully.']);
    }

    /**
     * Add internal private note to ticket
     */
    public function addInternalNote(Request $request, $uuid)
    {
        $request->validate(['message_body' => 'required|string']);

        $ticket = DB::table('support_tickets')->where('uuid', $uuid)->first();
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket not found'], 404);
        }

        DB::table('support_ticket_messages')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'ticket_id' => $ticket->id,
            'sender_type' => 'PLATFORM_ADMIN',
            'platform_admin_id' => $request->user()->id ?? 1,
            'message_type' => 'SYSTEM',
            'message_body' => $request->input('message_body'),
            'is_internal_note' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Internal note added successfully.']);
    }
}
