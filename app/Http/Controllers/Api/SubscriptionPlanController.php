<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    private function withRelations()
    {
        return SubscriptionPlan::with([
            'currency',
            'limits',
            'modules',
            'features',
            'planModules.module',
            'planFeatures.feature',
        ])->orderBy('sort_order')->orderBy('id');
    }

    public function index()
    {
        return response()->json($this->withRelations()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_code' => 'required|string|max:50|unique:subscription_plans,plan_code',
            'plan_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'quarterly_price' => 'nullable|numeric|min:0',
            'half_yearly_price' => 'nullable|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'trial_days' => 'required|integer|min:0',
            'grace_days' => 'required|integer|min:0',
            'sort_order' => 'required|integer',
            'is_active' => 'boolean',
            'limits' => 'array',
            'limits.*.limit_code' => 'required|string|max:80',
            'limits.*.limit_value' => 'nullable|numeric',
            'limits.*.unlimited' => 'boolean',
            'module_ids' => 'array',
            'module_ids.*' => 'integer|exists:platform_modules,id',
            'feature_ids' => 'array',
            'feature_ids.*' => 'integer|exists:platform_features,id',
        ]);

        $plan = DB::transaction(function () use ($validated) {
            $plan = SubscriptionPlan::create([
                'uuid' => (string) Str::uuid(),
                'plan_code' => $validated['plan_code'],
                'plan_name' => $validated['plan_name'],
                'description' => $validated['description'] ?? null,
                'monthly_price' => $validated['monthly_price'],
                'quarterly_price' => $validated['quarterly_price'] ?? null,
                'half_yearly_price' => $validated['half_yearly_price'] ?? null,
                'yearly_price' => $validated['yearly_price'] ?? null,
                'currency_id' => $validated['currency_id'],
                'trial_days' => $validated['trial_days'],
                'grace_days' => $validated['grace_days'],
                'sort_order' => $validated['sort_order'],
                'is_active' => $validated['is_active'] ?? true,
            ]);
            $this->syncRelations($plan, $validated);
            return $plan;
        });

        return response()->json($this->withRelations()->findOrFail($plan->id), 201);
    }

    public function show(SubscriptionPlan $subscriptionPlan)
    {
        return response()->json($this->withRelations()->findOrFail($subscriptionPlan->id));
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'plan_code' => 'sometimes|required|string|max:50|unique:subscription_plans,plan_code,' . $subscriptionPlan->id,
            'plan_name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'monthly_price' => 'sometimes|required|numeric|min:0',
            'quarterly_price' => 'nullable|numeric|min:0',
            'half_yearly_price' => 'nullable|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'currency_id' => 'sometimes|required|exists:currencies,id',
            'trial_days' => 'sometimes|required|integer|min:0',
            'grace_days' => 'sometimes|required|integer|min:0',
            'sort_order' => 'sometimes|required|integer',
            'is_active' => 'boolean',
            'limits' => 'sometimes|array',
            'limits.*.limit_code' => 'required|string|max:80',
            'limits.*.limit_value' => 'nullable|numeric',
            'limits.*.unlimited' => 'boolean',
            'module_ids' => 'sometimes|array',
            'module_ids.*' => 'integer|exists:platform_modules,id',
            'feature_ids' => 'sometimes|array',
            'feature_ids.*' => 'integer|exists:platform_features,id',
        ]);

        DB::transaction(function () use ($subscriptionPlan, $validated) {
            $subscriptionPlan->update(collect($validated)->except(['limits','module_ids','feature_ids'])->all());
            $this->syncRelations($subscriptionPlan, $validated);
        });

        return response()->json($this->withRelations()->findOrFail($subscriptionPlan->id));
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->delete();
        return response()->json(['message' => 'Subscription plan deleted successfully.']);
    }

    private function syncRelations(SubscriptionPlan $plan, array $data): void
    {
        if (array_key_exists('limits', $data)) {
            $plan->limits()->delete();
            foreach ($data['limits'] ?? [] as $limit) {
                $plan->limits()->create([
                    'limit_code' => $limit['limit_code'],
                    'limit_value' => $limit['limit_value'] ?? null,
                    'unlimited' => $limit['unlimited'] ?? false,
                ]);
            }
        }
        if (array_key_exists('module_ids', $data)) {
            $plan->modules()->sync($data['module_ids'] ?? []);
        }
        if (array_key_exists('feature_ids', $data)) {
            $plan->features()->sync($data['feature_ids'] ?? []);
        }
    }
}
