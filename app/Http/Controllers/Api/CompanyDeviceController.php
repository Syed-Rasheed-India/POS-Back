<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyDevice;
use Illuminate\Http\Request;

class CompanyDeviceController extends Controller
{
    public function index()
    {
        return response()->json(
            CompanyDevice::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'required|exists:company_branches,id',
            'device_code' => 'required|string|max:100',
            'device_name' => 'nullable|string|max:150',
            'device_type' => 'nullable|string|max:30',
            'platform' => 'nullable|string|max:30',
            'app_version' => 'nullable|string|max:30',
            'last_seen_at' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'device_metadata' => 'nullable|array',
        ]);

        $record = CompanyDevice::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            CompanyDevice::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = CompanyDevice::findOrFail($id);

        $validated = $request->validate([
            'device_name' => 'nullable|string|max:150',
            'device_type' => 'nullable|string|max:30',
            'platform' => 'nullable|string|max:30',
            'app_version' => 'nullable|string|max:30',
            'last_seen_at' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'device_metadata' => 'nullable|array',
        ]);

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        CompanyDevice::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Device deleted successfully'
        ]);
    }
}