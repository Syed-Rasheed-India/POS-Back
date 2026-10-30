<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        return response()->json(
            Company::all()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_code' => 'required|string|max:30|unique:companies,company_code',
            'company_name' => 'required|string|max:150',
            'legal_name' => 'nullable|string|max:200',
            'tax_number' => 'nullable|string|max:100',
            'country_id' => 'nullable|integer|exists:countries,id',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'timezone' => 'required|string|max:50',
            'status' => 'nullable|string|max:20',
            'database_identifier' => 'nullable|string|max:100|unique:companies,database_identifier',
        ]);

        $company = Company::create($validated);

        return response()->json($company, 201);
    }

    public function show(string $id)
    {
        return response()->json(
            Company::findOrFail($id)
        );
    }

    public function update(Request $request, string $id)
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'company_code' => 'sometimes|required|string|max:30|unique:companies,company_code,' . $id,
            'company_name' => 'sometimes|required|string|max:150',
            'legal_name' => 'nullable|string|max:200',
            'tax_number' => 'nullable|string|max:100',
            'country_id' => 'nullable|integer|exists:countries,id',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'timezone' => 'sometimes|required|string|max:50',
            'status' => 'nullable|string|max:20',
            'database_identifier' => 'nullable|string|max:100|unique:companies,database_identifier,' . $id,
        ]);

        $company->update($validated);

        return response()->json($company);
    }

    public function destroy(string $id)
    {
        $company = Company::findOrFail($id);

        $company->delete();

        return response()->json([
            'message' => 'Company deleted successfully.'
        ]);
    }
}