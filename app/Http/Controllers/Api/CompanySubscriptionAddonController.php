<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\CompanySubscriptionAddon;
use Illuminate\Http\Request;

class CompanySubscriptionAddonController extends Controller
{
    public function index() { return response()->json(CompanySubscriptionAddon::with(['subscription.company','subscription.plan','addon.currency'])->orderByDesc('id')->get()); }
    public function store(Request $r) { $d=$r->validate(['company_subscription_id'=>'required|exists:company_subscriptions,id','addon_id'=>'required|exists:subscription_addons,id','quantity'=>'required|numeric|min:0','unit_price'=>'required|numeric|min:0','start_date'=>'required|date','end_date'=>'nullable|date|after_or_equal:start_date','status'=>'required|string|max:20']); $x=CompanySubscriptionAddon::create($d); return response()->json($x->load(['subscription.company','subscription.plan','addon.currency']),201); }
    public function show($id) { return response()->json(CompanySubscriptionAddon::with(['subscription.company','subscription.plan','addon.currency'])->findOrFail($id)); }
    public function update(Request $r,$id) { $x=CompanySubscriptionAddon::findOrFail($id); $d=$r->validate(['company_subscription_id'=>'sometimes|exists:company_subscriptions,id','addon_id'=>'sometimes|exists:subscription_addons,id','quantity'=>'sometimes|numeric|min:0','unit_price'=>'sometimes|numeric|min:0','start_date'=>'sometimes|date','end_date'=>'nullable|date','status'=>'sometimes|string|max:20']); $x->update($d); return response()->json($x->fresh(['subscription.company','subscription.plan','addon.currency'])); }
    public function destroy($id) { CompanySubscriptionAddon::findOrFail($id)->delete(); return response()->json(['message'=>'Company subscription addon deleted successfully.']); }
}
