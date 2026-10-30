<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionUsageLog;
use Illuminate\Http\Request;

class SubscriptionUsageLogController extends Controller
{
    public function index() { return response()->json(SubscriptionUsageLog::with(['company','subscription.plan'])->orderByDesc('id')->get()); }
    public function store(Request $r) { $d=$r->validate(['company_id'=>'required|exists:companies,id','subscription_id'=>'required|exists:company_subscriptions,id','usage_code'=>'required|string|max:80','previous_value'=>'numeric','new_value'=>'numeric','source'=>'nullable|string|max:50']); $d['previous_value']??=0; $d['new_value']??=0; $x=SubscriptionUsageLog::create($d); return response()->json($x->load(['company','subscription.plan']),201); }
    public function show($id) { return response()->json(SubscriptionUsageLog::with(['company','subscription.plan'])->findOrFail($id)); }
    public function update(Request $r,$id) { $x=SubscriptionUsageLog::findOrFail($id); $d=$r->validate(['company_id'=>'sometimes|exists:companies,id','subscription_id'=>'sometimes|exists:company_subscriptions,id','usage_code'=>'sometimes|string|max:80','previous_value'=>'sometimes|numeric','new_value'=>'sometimes|numeric','source'=>'nullable|string|max:50']); $x->update($d); return response()->json($x->fresh(['company','subscription.plan'])); }
    public function destroy($id) { SubscriptionUsageLog::findOrFail($id)->delete(); return response()->json(['message'=>'Subscription usage log deleted successfully.']); }
}
