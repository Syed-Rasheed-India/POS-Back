<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyTerminal;
use Illuminate\Http\Request;

class CompanyTerminalController extends Controller
{
    public function index()
    {
        return response()->json(
            CompanyTerminal::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'required|exists:company_branches,id',
            'terminal_code' => 'required|string|max:30',
            'terminal_name' => 'nullable|string|max:100',
            'device_id' => 'nullable|exists:company_devices,id',
            'status' => 'nullable|string|max:20',
        ]);

        $record = CompanyTerminal::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            CompanyTerminal::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = CompanyTerminal::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => 'sometimes|exists:company_branches,id',
            'terminal_code' => 'sometimes|string|max:30',
            'terminal_name' => 'nullable|string|max:100',
            'device_id' => 'nullable|exists:company_devices,id',
            'status' => 'nullable|string|max:20',
        ]);

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        CompanyTerminal::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Terminal deleted successfully'
        ]);
    }
}