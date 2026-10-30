<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlanLimit;
use Illuminate\Http\Request;

class SubscriptionPlanLimitController extends Controller
{
    public function index() { return response()->json(SubscriptionPlanLimit::with('plan')->get()); }
    public function store(Request $request) {
        $v=$request->validate(['plan_id'=>'required|exists:subscription_plans,id','limit_code'=>'required|string|max:80','limit_value'=>'nullable|numeric','unlimited'=>'boolean']);
        return response()->json(SubscriptionPlanLimit::create($v),201);
    }
    public function show(SubscriptionPlanLimit $subscriptionPlanLimit) { return response()->json($subscriptionPlanLimit->load('plan')); }
    public function update(Request $request, SubscriptionPlanLimit $subscriptionPlanLimit) {
        $v=$request->validate(['plan_id'=>'sometimes|exists:subscription_plans,id','limit_code'=>'sometimes|string|max:80','limit_value'=>'nullable|numeric','unlimited'=>'boolean']);
        $subscriptionPlanLimit->update($v); return response()->json($subscriptionPlanLimit);
    }
    public function destroy(SubscriptionPlanLimit $subscriptionPlanLimit) { $subscriptionPlanLimit->delete(); return response()->json(['message'=>'Subscription plan limit deleted successfully.']); }
}
