<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyStatusHistory;
use Illuminate\Http\Request;

class CompanyStatusHistoryController extends Controller
{
    public function index()
    {
        return response()->json(
            CompanyStatusHistory::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'old_status' => 'nullable|string|max:20',
            'new_status' => 'required|string|max:20',
            'reason' => 'nullable|string',
            'changed_by' => 'nullable|exists:platform_admins,id',
            'changed_at' => 'nullable|date',
        ]);

        $record = CompanyStatusHistory::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            CompanyStatusHistory::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = CompanyStatusHistory::findOrFail($id);

        $validated = $request->validate([
            'old_status' => 'nullable|string|max:20',
            'new_status' => 'sometimes|string|max:20',
            'reason' => 'nullable|string',
            'changed_by' => 'nullable|exists:platform_admins,id',
            'changed_at' => 'nullable|date',
        ]);

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        CompanyStatusHistory::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Company status history deleted successfully'
        ]);
    }
}