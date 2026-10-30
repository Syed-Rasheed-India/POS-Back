<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionUsageController extends Controller
{
    public function index() { return response()->json(SubscriptionUsage::with(['company','subscription.plan'])->orderByDesc('id')->get()); }
    public function store(Request $r) { $d=$r->validate(['company_id'=>'required|exists:companies,id','subscription_id'=>'required|exists:company_subscriptions,id','usage_code'=>'required|string|max:80','usage_value'=>'required|numeric|min:0','last_calculated_at'=>'sometimes|date']); $d['last_calculated_at']??=now(); $x=SubscriptionUsage::create($d); return response()->json($x->load(['company','subscription.plan']),201); }
    public function show($id) { return response()->json(SubscriptionUsage::with(['company','subscription.plan'])->findOrFail($id)); }
    public function update(Request $r,$id) { return DB::transaction(function() use($r,$id){ $x=SubscriptionUsage::findOrFail($id); $d=$r->validate(['company_id'=>'sometimes|exists:companies,id','subscription_id'=>'sometimes|exists:company_subscriptions,id','usage_code'=>'sometimes|string|max:80','usage_value'=>'sometimes|numeric|min:0','last_calculated_at'=>'sometimes|date']); $x->update($d); return response()->json($x->fresh(['company','subscription.plan'])); }); }
    public function destroy($id) { SubscriptionUsage::findOrFail($id)->delete(); return response()->json(['message'=>'Subscription usage deleted successfully.']); }
}
