<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyWarehouse;
use Illuminate\Http\Request;

class CompanyWarehouseController extends Controller
{
    public function index()
    {
        return response()->json(
            CompanyWarehouse::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'required|exists:company_branches,id',
            'warehouse_code' => 'required|string|max:30',
            'warehouse_name' => 'required|string|max:150',
            'status' => 'nullable|string|max:20',
        ]);

        $record = CompanyWarehouse::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            CompanyWarehouse::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = CompanyWarehouse::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => 'sometimes|exists:company_branches,id',
            'warehouse_code' => 'sometimes|string|max:30',
            'warehouse_name' => 'sometimes|string|max:150',
            'status' => 'nullable|string|max:20',
        ]);

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        CompanyWarehouse::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Warehouse deleted successfully'
        ]);
    }
}