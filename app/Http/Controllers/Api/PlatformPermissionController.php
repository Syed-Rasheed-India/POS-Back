<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformPermission;
use Illuminate\Http\Request;

class PlatformPermissionController extends Controller
{
    public function index()
    {
        return response()->json(
            PlatformPermission::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'permission_code' => 'required|string|max:100|unique:platform_permissions,permission_code',
            'permission_name' => 'required|string|max:150',
            'module' => 'nullable|string|max:50',
            'action' => 'nullable|string|max:30',
            'description' => 'nullable|string',
        ]);

        $permission = PlatformPermission::create($validated);

        return response()->json($permission, 201);
    }

    public function show($id)
    {
        return response()->json(
            PlatformPermission::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $permission = PlatformPermission::findOrFail($id);

        $validated = $request->validate([
            'permission_code' => 'sometimes|string|max:100|unique:platform_permissions,permission_code,' . $id,
            'permission_name' => 'sometimes|string|max:150',
            'module' => 'nullable|string|max:50',
            'action' => 'nullable|string|max:30',
            'description' => 'nullable|string',
        ]);

        $permission->update($validated);

        return response()->json($permission);
    }

    public function destroy($id)
    {
        PlatformPermission::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Permission deleted successfully'
        ]);
    }
}