<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    public function index()
    {
        return response()->json(
            FeatureFlag::with('feature')
                ->orderBy('id', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'feature_id' => 'required|exists:platform_features,id',
            'flag_key' => 'required|string|max:100|unique:feature_flags,flag_key',
            'is_enabled' => 'boolean',
            'rollout_percentage' => 'numeric|min:0|max:100',
            'conditions' => 'nullable|array',
        ]);

        $flag = FeatureFlag::create($validated);

        return response()->json([
            'message' => 'Feature flag created successfully',
            'data' => $flag->load('feature')
        ], 201);
    }

    public function show(FeatureFlag $featureFlag)
    {
        return response()->json(
            $featureFlag->load('feature')
        );
    }

    public function update(
        Request $request,
        FeatureFlag $featureFlag
    ) {
        $validated = $request->validate([
            'feature_id' => 'sometimes|exists:platform_features,id',
            'flag_key' => 'sometimes|string|max:100|unique:feature_flags,flag_key,' . $featureFlag->id,
            'is_enabled' => 'boolean',
            'rollout_percentage' => 'numeric|min:0|max:100',
            'conditions' => 'nullable|array',
        ]);

        $featureFlag->update($validated);

        return response()->json([
            'message' => 'Feature flag updated successfully',
            'data' => $featureFlag->load('feature')
        ]);
    }

    public function destroy(FeatureFlag $featureFlag)
    {
        $featureFlag->delete();

        return response()->json([
            'message' => 'Feature flag deleted successfully'
        ]);
    }
}