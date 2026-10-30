<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyAddress;
use Illuminate\Http\Request;

class CompanyAddressController extends Controller
{
    public function index()
    {
        return response()->json(
            CompanyAddress::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'address_type' => 'nullable|string|max:30',
            'address_line_1' => 'nullable|string|max:200',
            'address_line_2' => 'nullable|string|max:200',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country_id' => 'required|exists:countries,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_primary' => 'boolean',
        ]);

        $record = CompanyAddress::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            CompanyAddress::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = CompanyAddress::findOrFail($id);

        $validated = $request->validate([
            'address_type' => 'nullable|string|max:30',
            'address_line_1' => 'nullable|string|max:200',
            'address_line_2' => 'nullable|string|max:200',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country_id' => 'sometimes|exists:countries,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_primary' => 'boolean',
        ]);

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        CompanyAddress::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Company address deleted successfully'
        ]);
    }
}