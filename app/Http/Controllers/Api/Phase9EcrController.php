<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyTerminal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Phase9EcrController extends Controller
{
    /**
     * No provider catalog table exists yet.
     * Return an empty list rather than presenting mock records as real data.
     */
    public function getProviders(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'No ECR provider catalog is configured yet.',
        ]);
    }

    /**
     * Return actual terminal records from the database.
     */
    public function getTerminals(Request $request)
    {
        $terminals = CompanyTerminal::query()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $terminals,
        ]);
    }

    /**
     * Save a terminal using the existing company_terminals table.
     */
    public function mapTerminal(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'branch_id' => [
                'required',
                'integer',
                Rule::exists('company_branches', 'id')
                    ->where('company_id', $request->input('company_id')),
            ],
            'terminal_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('company_terminals', 'terminal_code')
                    ->where('company_id', $request->input('company_id')),
            ],
            'terminal_name' => ['required', 'string', 'max:100'],
            'device_id' => ['nullable', 'integer', 'exists:company_devices,id'],
            'status' => ['sometimes', 'string', 'in:ACTIVE,INACTIVE'],
        ]);

        // Ensure a selected device belongs to the same company.
        if (!empty($validated['device_id'])) {
            $deviceBelongsToCompany = \Illuminate\Support\Facades\DB::table('company_devices')
                ->where('id', $validated['device_id'])
                ->where('company_id', $validated['company_id'])
                ->exists();

            if (!$deviceBelongsToCompany) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected device does not belong to this company.',
                ], 422);
            }
        }

        $terminal = CompanyTerminal::create([
            'company_id' => $validated['company_id'],
            'branch_id' => $validated['branch_id'],
            'terminal_code' => $validated['terminal_code'],
            'terminal_name' => $validated['terminal_name'],
            'device_id' => $validated['device_id'] ?? null,
            'status' => $validated['status'] ?? 'ACTIVE',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terminal saved successfully.',
            'data' => $terminal->fresh(),
        ], 201);
    }
}
