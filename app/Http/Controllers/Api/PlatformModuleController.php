<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformModule;
use Illuminate\Http\Request;

class PlatformModuleController extends Controller
{
    public function index()
    {
        return response()->json(
            PlatformModule::with('features')
                ->orderBy('sort_order')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_code' => 'required|string|max:50|unique:platform_modules,module_code',
            'module_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $module = PlatformModule::create($validated);

        return response()->json([
            'message' => 'Platform module created successfully',
            'data' => $module
        ], 201);
    }

    public function show(PlatformModule $platformModule)
    {
        return response()->json(
            $platformModule->load('features')
        );
    }

    public function update(
        Request $request,
        PlatformModule $platformModule
    ) {
        $validated = $request->validate([
            'module_code' => 'sometimes|string|max:50|unique:platform_modules,module_code,' . $platformModule->id,
            'module_name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $platformModule->update($validated);

        return response()->json([
            'message' => 'Platform module updated successfully',
            'data' => $platformModule
        ]);
    }

    public function destroy(PlatformModule $platformModule)
    {
        $platformModule->delete();

        return response()->json([
            'message' => 'Platform module deleted successfully'
        ]);
    }
}