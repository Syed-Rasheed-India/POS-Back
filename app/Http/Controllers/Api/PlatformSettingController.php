<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class PlatformSettingController extends Controller
{
    public function index()
    {
        return response()->json(
            PlatformSetting::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'setting_key' => 'required|string|max:100|unique:platform_settings,setting_key',
            'setting_value' => 'nullable|string',
            'value_type' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $setting = PlatformSetting::create($validated);

        return response()->json([
            'message' => 'Platform setting created successfully',
            'data' => $setting
        ], 201);
    }

    public function show(PlatformSetting $platformSetting)
    {
        return response()->json($platformSetting);
    }

    public function update(
        Request $request,
        PlatformSetting $platformSetting
    ) {
        $validated = $request->validate([
            'setting_key' => 'sometimes|string|max:100|unique:platform_settings,setting_key,' . $platformSetting->id,
            'setting_value' => 'nullable|string',
            'value_type' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $platformSetting->update($validated);

        return response()->json([
            'message' => 'Platform setting updated successfully',
            'data' => $platformSetting
        ]);
    }

    public function destroy(PlatformSetting $platformSetting)
    {
        $platformSetting->delete();

        return response()->json([
            'message' => 'Platform setting deleted successfully'
        ]);
    }
}