<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;

class CompanySettingController extends Controller
{
    public function index()
    {
        return response()->json(
            CompanySetting::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'setting_key' => 'required|string|max:100',
            'setting_value' => 'nullable|array',
        ]);

        $record = CompanySetting::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            CompanySetting::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = CompanySetting::findOrFail($id);

        $validated = $request->validate([
            'setting_key' => 'sometimes|string|max:100',
            'setting_value' => 'nullable|array',
        ]);

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        CompanySetting::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Company setting deleted successfully'
        ]);
    }
}