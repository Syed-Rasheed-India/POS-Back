<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdminUserRole;
use Illuminate\Http\Request;

class PlatformAdminUserRoleController extends Controller
{
    public function index()
    {
        return response()->json(
            PlatformAdminUserRole::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'required|exists:platform_admins,id',
            'role_id' => 'required|exists:platform_admin_roles,id',
        ]);

        $record = PlatformAdminUserRole::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            PlatformAdminUserRole::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = PlatformAdminUserRole::findOrFail($id);

        $validated = $request->validate([
            'admin_id' => 'sometimes|exists:platform_admins,id',
            'role_id' => 'sometimes|exists:platform_admin_roles,id',
        ]);

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        PlatformAdminUserRole::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Admin role assignment deleted successfully'
        ]);
    }
}