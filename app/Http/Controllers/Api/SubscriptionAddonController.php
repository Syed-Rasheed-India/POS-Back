<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionAddonController extends Controller
{
    public function index() { return response()->json(SubscriptionAddon::with('currency')->orderBy('addon_name')->get()); }
    public function store(Request $r) { $d=$r->validate(['uuid'=>'sometimes|uuid|unique:subscription_addons,uuid','addon_code'=>'required|string|max:50|unique:subscription_addons,addon_code','addon_name'=>'required|string|max:100','description'=>'nullable|string','price'=>'required|numeric|min:0','currency_id'=>'required|exists:currencies,id','billing_cycle'=>'required|string|max:20','is_active'=>'boolean']); $d['uuid']??=(string)Str::uuid(); $d['is_active']??=true; return response()->json(SubscriptionAddon::create($d)->load('currency'),201); }
    public function show($id) { return response()->json(SubscriptionAddon::with('currency')->findOrFail($id)); }
    public function update(Request $r,$id) { $x=SubscriptionAddon::findOrFail($id); $d=$r->validate(['uuid'=>'sometimes|uuid|unique:subscription_addons,uuid,'.$id,'addon_code'=>'sometimes|string|max:50|unique:subscription_addons,addon_code,'.$id,'addon_name'=>'sometimes|string|max:100','description'=>'nullable|string','price'=>'sometimes|numeric|min:0','currency_id'=>'sometimes|exists:currencies,id','billing_cycle'=>'sometimes|string|max:20','is_active'=>'boolean']); $x->update($d); return response()->json($x->fresh('currency')); }
    public function destroy($id) { SubscriptionAddon::findOrFail($id)->delete(); return response()->json(['message'=>'Subscription addon deleted successfully.']); }
}
