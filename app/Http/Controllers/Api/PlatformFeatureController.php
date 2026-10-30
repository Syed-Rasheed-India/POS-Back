<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformFeature;
use Illuminate\Http\Request;

class PlatformFeatureController extends Controller
{
    public function index()
    {
        return response()->json(
            PlatformFeature::with('module')
                ->orderBy('id', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:platform_modules,id',
            'feature_code' => 'required|string|max:80',
            'feature_name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $feature = PlatformFeature::create($validated);

        return response()->json([
            'message' => 'Platform feature created successfully',
            'data' => $feature->load('module')
        ], 201);
    }

    public function show(PlatformFeature $platformFeature)
    {
        return response()->json(
            $platformFeature->load([
                'module',
                'featureFlag'
            ])
        );
    }

    public function update(
        Request $request,
        PlatformFeature $platformFeature
    ) {
        $validated = $request->validate([
            'module_id' => 'sometimes|exists:platform_modules,id',
            'feature_code' => 'sometimes|string|max:80',
            'feature_name' => 'sometimes|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $platformFeature->update($validated);

        return response()->json([
            'message' => 'Platform feature updated successfully',
            'data' => $platformFeature->load('module')
        ]);
    }

    public function destroy(PlatformFeature $platformFeature)
    {
        $platformFeature->delete();

        return response()->json([
            'message' => 'Platform feature deleted successfully'
        ]);
    }
}