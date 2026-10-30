<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdminLoginLog;
use Illuminate\Http\Request;

class PlatformAdminLoginLogController extends Controller
{
    public function index()
    {
        return response()->json(
            PlatformAdminLoginLog::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'nullable|exists:platform_admins,id',
            'username' => 'nullable|string|max:50',
            'login_status' => 'required|string|max:20',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string',
            'failure_reason' => 'nullable|string|max:255',
        ]);

        $log = PlatformAdminLoginLog::create($validated);

        return response()->json($log, 201);
    }

    public function show($id)
    {
        return response()->json(
            PlatformAdminLoginLog::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $log = PlatformAdminLoginLog::findOrFail($id);

        $validated = $request->validate([
            'login_status' => 'sometimes|string|max:20',
            'failure_reason' => 'nullable|string|max:255',
        ]);

        $log->update($validated);

        return response()->json($log);
    }

    public function destroy($id)
    {
        PlatformAdminLoginLog::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Login log deleted successfully'
        ]);
    }
}