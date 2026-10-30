<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PlatformAdminController extends Controller
{
    public function index()
    {
        return response()->json(
            PlatformAdmin::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:platform_admins,username',
            'email' => 'required|email|max:150|unique:platform_admins,email',
            'password' => 'required|string|min:8',

            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',

            'status' => 'nullable|string|max:20',
        ]);

        $validated['password_hash'] = Hash::make($validated['password']);

        unset($validated['password']);

        $admin = PlatformAdmin::create($validated);

        return response()->json($admin, 201);
    }

    public function show($id)
    {
        $admin = PlatformAdmin::findOrFail($id);

        return response()->json($admin);
    }

    public function update(Request $request, $id)
    {
        $admin = PlatformAdmin::findOrFail($id);

        $validated = $request->validate([
            'username' => 'sometimes|string|max:50|unique:platform_admins,username,' . $id,
            'email' => 'sometimes|email|max:150|unique:platform_admins,email,' . $id,

            'password' => 'nullable|string|min:8',

            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',

            'status' => 'nullable|string|max:20',
        ]);

        if (isset($validated['password'])) {
            $validated['password_hash'] = Hash::make(
                $validated['password']
            );

            $validated['password_changed_at'] = now();

            unset($validated['password']);
        }

        $admin->update($validated);

        return response()->json($admin);
    }

    public function destroy($id)
    {
        $admin = PlatformAdmin::findOrFail($id);

        $admin->delete();

        return response()->json([
            'message' => 'Platform admin deleted successfully'
        ]);
    }
}