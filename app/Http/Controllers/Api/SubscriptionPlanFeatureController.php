<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlanFeature;
use Illuminate\Http\Request;

class SubscriptionPlanFeatureController extends Controller
{
    public function index() { return response()->json(SubscriptionPlanFeature::with(['plan','feature'])->get()); }
    public function store(Request $request) { $v=$request->validate(['plan_id'=>'required|exists:subscription_plans,id','feature_id'=>'required|exists:platform_features,id']); return response()->json(SubscriptionPlanFeature::create($v),201); }
    public function show(SubscriptionPlanFeature $subscriptionPlanFeature) { return response()->json($subscriptionPlanFeature->load(['plan','feature'])); }
    public function update(Request $request, SubscriptionPlanFeature $subscriptionPlanFeature) { $v=$request->validate(['plan_id'=>'sometimes|exists:subscription_plans,id','feature_id'=>'sometimes|exists:platform_features,id']); $subscriptionPlanFeature->update($v); return response()->json($subscriptionPlanFeature); }
    public function destroy(SubscriptionPlanFeature $subscriptionPlanFeature) { $subscriptionPlanFeature->delete(); return response()->json(['message'=>'Subscription plan feature deleted successfully.']); }
}
