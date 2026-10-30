<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionLimitSnapshot;
use Illuminate\Http\Request;

class SubscriptionLimitSnapshotController extends Controller
{
    public function index() { return response()->json(SubscriptionLimitSnapshot::with('subscription.plan')->orderByDesc('effective_from')->get()); }
    public function store(Request $r) { $d=$r->validate(['subscription_id'=>'required|exists:company_subscriptions,id','limit_code'=>'required|string|max:80','limit_value'=>'nullable|numeric','unlimited'=>'boolean','effective_from'=>'sometimes|date','effective_to'=>'nullable|date']); $d['unlimited']??=false; $d['effective_from']??=now(); return response()->json(SubscriptionLimitSnapshot::create($d),201); }
    public function show($id) { return response()->json(SubscriptionLimitSnapshot::with('subscription.plan')->findOrFail($id)); }
    public function update(Request $r,$id) { $x=SubscriptionLimitSnapshot::findOrFail($id); $d=$r->validate(['subscription_id'=>'sometimes|exists:company_subscriptions,id','limit_code'=>'sometimes|string|max:80','limit_value'=>'nullable|numeric','unlimited'=>'boolean','effective_from'=>'sometimes|date','effective_to'=>'nullable|date']); $x->update($d); return response()->json($x->fresh('subscription.plan')); }
    public function destroy($id) { SubscriptionLimitSnapshot::findOrFail($id)->delete(); return response()->json(['message'=>'Subscription limit snapshot deleted successfully.']); }
}
