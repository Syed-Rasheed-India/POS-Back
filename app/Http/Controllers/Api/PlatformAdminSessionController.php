<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdminSession;
use Illuminate\Http\Request;

class PlatformAdminSessionController extends Controller
{
    public function index()
    {
        return response()->json(
            PlatformAdminSession::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'required|exists:platform_admins,id',
            'token_hash' => 'required|string|max:255|unique:platform_admin_sessions,token_hash',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string',
            'device_info' => 'nullable|array',
            'expires_at' => 'nullable|date',
            'last_activity_at' => 'nullable|date',
            'revoked_at' => 'nullable|date',
        ]);

        $session = PlatformAdminSession::create($validated);

        return response()->json($session, 201);
    }

    public function show($id)
    {
        return response()->json(
            PlatformAdminSession::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $session = PlatformAdminSession::findOrFail($id);

        $validated = $request->validate([
            'expires_at' => 'nullable|date',
            'last_activity_at' => 'nullable|date',
            'revoked_at' => 'nullable|date',
            'device_info' => 'nullable|array',
        ]);

        $session->update($validated);

        return response()->json($session);
    }

    public function destroy($id)
    {
        PlatformAdminSession::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Session deleted successfully'
        ]);
    }
}