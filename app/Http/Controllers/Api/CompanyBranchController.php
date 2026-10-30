<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyBranch;
use Illuminate\Http\Request;

class CompanyBranchController extends Controller
{
    public function index()
    {
        return response()->json(
            CompanyBranch::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'branch_code' => 'required|string|max:30',
            'branch_name' => 'required|string|max:150',
            'address_id' => 'nullable|exists:company_addresses,id',
            'status' => 'nullable|string|max:20',
        ]);

        $record = CompanyBranch::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            CompanyBranch::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = CompanyBranch::findOrFail($id);

        $validated = $request->validate([
            'branch_code' => 'sometimes|string|max:30',
            'branch_name' => 'sometimes|string|max:150',
            'address_id' => 'nullable|exists:company_addresses,id',
            'status' => 'nullable|string|max:20',
        ]);

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        CompanyBranch::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Company branch deleted successfully'
        ]);
    }
}