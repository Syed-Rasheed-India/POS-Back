<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformConfig;
use Illuminate\Http\Request;

class PlatformConfigController extends Controller
{
    public function index()
    {
        return response()->json(
            PlatformConfig::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'config_group' => 'required|string|max:50',
            'config_key' => 'required|string|max:100',
            'config_value' => 'nullable|array',
            'environment' => 'required|string|max:20',
            'is_encrypted' => 'boolean',
        ]);

        $config = PlatformConfig::create($validated);

        return response()->json([
            'message' => 'Platform config created successfully',
            'data' => $config
        ], 201);
    }

    public function show(PlatformConfig $platformConfig)
    {
        return response()->json($platformConfig);
    }

    public function update(
        Request $request,
        PlatformConfig $platformConfig
    ) {
        $validated = $request->validate([
            'config_group' => 'sometimes|string|max:50',
            'config_key' => 'sometimes|string|max:100',
            'config_value' => 'nullable|array',
            'environment' => 'sometimes|string|max:20',
            'is_encrypted' => 'boolean',
        ]);

        $platformConfig->update($validated);

        return response()->json([
            'message' => 'Platform config updated successfully',
            'data' => $platformConfig
        ]);
    }

    public function destroy(PlatformConfig $platformConfig)
    {
        $platformConfig->delete();

        return response()->json([
            'message' => 'Platform config deleted successfully'
        ]);
    }
}