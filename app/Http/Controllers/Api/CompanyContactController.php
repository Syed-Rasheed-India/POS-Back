<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyContact;
use Illuminate\Http\Request;

class CompanyContactController extends Controller
{
    public function index()
    {
        return response()->json(
            CompanyContact::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'contact_type' => 'nullable|string|max:30',
            'contact_name' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'designation' => 'nullable|string|max:100',
            'is_primary' => 'boolean',
        ]);

        $record = CompanyContact::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            CompanyContact::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = CompanyContact::findOrFail($id);

        $validated = $request->validate([
            'contact_type' => 'nullable|string|max:30',
            'contact_name' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'designation' => 'nullable|string|max:100',
            'is_primary' => 'boolean',
        ]);

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        CompanyContact::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Company contact deleted successfully'
        ]);
    }
}