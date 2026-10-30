<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionGracePeriod;
use Illuminate\Http\Request;

class SubscriptionGracePeriodController extends Controller
{
    public function index() { return response()->json(SubscriptionGracePeriod::with(['company','subscription.plan','approver'])->orderByDesc('id')->get()); }
    public function store(Request $r) { $d=$r->validate(['company_id'=>'required|exists:companies,id','subscription_id'=>'required|exists:company_subscriptions,id','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','reason'=>'nullable|string','approved_by'=>'nullable|exists:platform_admins,id']); $x=SubscriptionGracePeriod::create($d); return response()->json($x->load(['company','subscription.plan','approver']),201); }
    public function show($id) { return response()->json(SubscriptionGracePeriod::with(['company','subscription.plan','approver'])->findOrFail($id)); }
    public function update(Request $r,$id) { $x=SubscriptionGracePeriod::findOrFail($id); $d=$r->validate(['company_id'=>'sometimes|exists:companies,id','subscription_id'=>'sometimes|exists:company_subscriptions,id','start_date'=>'sometimes|date','end_date'=>'sometimes|date','reason'=>'nullable|string','approved_by'=>'nullable|exists:platform_admins,id']); $x->update($d); return response()->json($x->fresh(['company','subscription.plan','approver'])); }
    public function destroy($id) { SubscriptionGracePeriod::findOrFail($id)->delete(); return response()->json(['message'=>'Subscription grace period deleted successfully.']); }
}
