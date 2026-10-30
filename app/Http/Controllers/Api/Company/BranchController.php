<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyBranch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    private function branches(Request $request)
    {
        return CompanyBranch::query()
            ->where('company_id', $request->user()->company_id);
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->branches($request)
                ->orderByDesc('id')
                ->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'branch_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('company_branches', 'branch_code')
                    ->where('company_id', $companyId),
            ],
            'branch_name' => 'required|string|max:150',
            'address_id' => [
                'nullable',
                'integer',
                Rule::exists('company_addresses', 'id')
                    ->where('company_id', $companyId),
            ],
            'status' => 'sometimes|string|in:ACTIVE,INACTIVE',
        ]);

        $branch = CompanyBranch::create([
            ...$validated,
            'company_id' => $companyId,
        ]);

        return response()->json($branch, 201);
    }

    public function show(Request $request, int $id)
    {
        return response()->json(
            $this->branches($request)->findOrFail($id)
        );
    }

    public function update(Request $request, int $id)
    {
        $branch = $this->branches($request)->findOrFail($id);
        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'branch_code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('company_branches', 'branch_code')
                    ->where('company_id', $companyId)
                    ->ignore($branch->id),
            ],
            'branch_name' => 'sometimes|required|string|max:150',
            'address_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('company_addresses', 'id')
                    ->where('company_id', $companyId),
            ],
            'status' => 'sometimes|required|string|in:ACTIVE,INACTIVE',
        ]);

        $branch->update($validated);

        return response()->json($branch->fresh());
    }

    public function destroy(Request $request, int $id)
    {
        $branch = $this->branches($request)->findOrFail($id);
        $branch->delete();

        return response()->json([
            'message' => 'Branch deleted successfully.',
        ]);
    }
}
