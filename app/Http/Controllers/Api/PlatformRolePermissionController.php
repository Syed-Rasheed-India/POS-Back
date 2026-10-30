<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformRolePermission;
use Illuminate\Http\Request;

class PlatformRolePermissionController extends Controller
{
    public function index()
    {
        return response()->json(
            PlatformRolePermission::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:platform_admin_roles,id',
            'permission_id' => 'required|exists:platform_permissions,id',
        ]);

        $record = PlatformRolePermission::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            PlatformRolePermission::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = PlatformRolePermission::findOrFail($id);

        $validated = $request->validate([
            'role_id' => 'sometimes|exists:platform_admin_roles,id',
            'permission_id' => 'sometimes|exists:platform_permissions,id',
        ]);

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        PlatformRolePermission::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Role permission deleted successfully'
        ]);
    }
}