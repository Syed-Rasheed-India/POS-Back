<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdminRole;
use Illuminate\Http\Request;

class PlatformAdminRoleController extends Controller
{
    public function index()
    {
        return response()->json(
            PlatformAdminRole::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_code' => 'required|string|max:50|unique:platform_admin_roles,role_code',
            'role_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_system_role' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $role = PlatformAdminRole::create($validated);

        return response()->json($role, 201);
    }

    public function show($id)
    {
        return response()->json(
            PlatformAdminRole::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $role = PlatformAdminRole::findOrFail($id);

        $validated = $request->validate([
            'role_code' => 'sometimes|string|max:50|unique:platform_admin_roles,role_code,' . $id,
            'role_name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'is_system_role' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $role->update($validated);

        return response()->json($role);
    }

    public function destroy($id)
    {
        $role = PlatformAdminRole::findOrFail($id);

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }
}