<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanySubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanySubscriptionController extends Controller
{
    private function query()
    {
        return CompanySubscription::with([
            'company', 'plan.currency', 'currency',
            'limitSnapshots', 'usage', 'usageLogs',
            'addons.addon', 'trials', 'gracePeriods.approver',
            'limitOverrides.createdBy',
        ])->orderByDesc('id');
    }

    private function rules(Request $request, ?int $id = null): array
    {
        return [
            'uuid' => 'sometimes|uuid|unique:company_subscriptions,uuid' . ($id ? ',' . $id : ''),
            'company_id' => 'required|exists:companies,id',
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|max:20',
            'auto_renew' => 'boolean',
            'price' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
        ];
    }

    public function index() { return response()->json($this->query()->get()); }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules($request));
        $data['uuid'] ??= (string) Str::uuid();
        $data['auto_renew'] ??= true;
        return response()->json($this->query()->findOrFail(CompanySubscription::create($data)->id), 201);
    }

    public function show($id) { return response()->json($this->query()->findOrFail($id)); }

    public function update(Request $request, $id)
    {
        $record = CompanySubscription::findOrFail($id);
        $rules = $this->rules($request, (int) $id);
        foreach ($rules as $key => $rule) $rules[$key] = str_replace('required|', 'sometimes|', $rule);
        $data = $request->validate($rules);
        $record->update($data);
        return response()->json($this->query()->findOrFail($record->id));
    }

    public function destroy($id)
    {
        CompanySubscription::findOrFail($id)->delete();
        return response()->json(['message' => 'Company subscription deleted successfully.']);
    }
}
