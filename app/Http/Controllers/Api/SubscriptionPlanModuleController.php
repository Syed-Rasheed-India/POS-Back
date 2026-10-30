<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlanModule;
use Illuminate\Http\Request;

class SubscriptionPlanModuleController extends Controller
{
    public function index() { return response()->json(SubscriptionPlanModule::with(['plan','module'])->get()); }
    public function store(Request $request) { $v=$request->validate(['plan_id'=>'required|exists:subscription_plans,id','module_id'=>'required|exists:platform_modules,id']); return response()->json(SubscriptionPlanModule::create($v),201); }
    public function show(SubscriptionPlanModule $subscriptionPlanModule) { return response()->json($subscriptionPlanModule->load(['plan','module'])); }
    public function update(Request $request, SubscriptionPlanModule $subscriptionPlanModule) { $v=$request->validate(['plan_id'=>'sometimes|exists:subscription_plans,id','module_id'=>'sometimes|exists:platform_modules,id']); $subscriptionPlanModule->update($v); return response()->json($subscriptionPlanModule); }
    public function destroy(SubscriptionPlanModule $subscriptionPlanModule) { $subscriptionPlanModule->delete(); return response()->json(['message'=>'Subscription plan module deleted successfully.']); }
}
