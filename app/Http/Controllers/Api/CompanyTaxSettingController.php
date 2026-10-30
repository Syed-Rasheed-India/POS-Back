<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyTaxSetting;
use Illuminate\Http\Request;

class CompanyTaxSettingController extends Controller
{
    public function index()
    {
        return response()->json(
            CompanyTaxSetting::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'country_id' => 'required|exists:countries,id',

            'tax_registration_number' => 'nullable|string|max:100',
            'tax_system_name' => 'nullable|string|max:100',

            'default_tax_rate' => 'nullable|numeric|min:0|max:100',

            'tax_inclusive' => 'boolean',
            'tax_enabled' => 'boolean',

            'tax_configuration' => 'nullable|array',

            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date',

            'status' => 'nullable|string|max:20',

            'created_by' => 'nullable|exists:platform_admins,id',
            'updated_by' => 'nullable|exists:platform_admins,id',
        ]);

        $record = CompanyTaxSetting::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            CompanyTaxSetting::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = CompanyTaxSetting::findOrFail($id);

        $validated = $request->validate([
            'country_id' => 'sometimes|exists:countries,id',

            'tax_registration_number' => 'nullable|string|max:100',
            'tax_system_name' => 'nullable|string|max:100',

            'default_tax_rate' => 'nullable|numeric|min:0|max:100',

            'tax_inclusive' => 'boolean',
            'tax_enabled' => 'boolean',

            'tax_configuration' => 'nullable|array',

            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date',

            'status' => 'nullable|string|max:20',

            'updated_by' => 'nullable|exists:platform_admins,id',
        ]);

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        CompanyTaxSetting::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Company tax setting deleted successfully'
        ]);
    }
}