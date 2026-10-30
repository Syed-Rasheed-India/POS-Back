<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionChange;
use Illuminate\Http\Request;

class SubscriptionChangeController extends Controller
{
    private function query() { return SubscriptionChange::with(['company','oldSubscription.plan','newSubscription.plan','oldPlan','newPlan','changedBy'])->orderByDesc('changed_at')->orderByDesc('id'); }
    public function index() { return response()->json($this->query()->get()); }
    public function store(Request $r) { $d=$r->validate(['company_id'=>'required|exists:companies,id','old_subscription_id'=>'nullable|exists:company_subscriptions,id','new_subscription_id'=>'nullable|exists:company_subscriptions,id','change_type'=>'required|string|max:30','old_plan_id'=>'nullable|exists:subscription_plans,id','new_plan_id'=>'nullable|exists:subscription_plans,id','reason'=>'nullable|string','changed_by'=>'nullable|exists:platform_admins,id','changed_at'=>'sometimes|date']); $d['changed_at']??=now(); return response()->json($this->query()->findOrFail(SubscriptionChange::create($d)->id),201); }
    public function show($id) { return response()->json($this->query()->findOrFail($id)); }
    public function update(Request $r,$id) { $x=SubscriptionChange::findOrFail($id); $d=$r->validate(['company_id'=>'sometimes|exists:companies,id','old_subscription_id'=>'nullable|exists:company_subscriptions,id','new_subscription_id'=>'nullable|exists:company_subscriptions,id','change_type'=>'sometimes|string|max:30','old_plan_id'=>'nullable|exists:subscription_plans,id','new_plan_id'=>'nullable|exists:subscription_plans,id','reason'=>'nullable|string','changed_by'=>'nullable|exists:platform_admins,id','changed_at'=>'sometimes|date']); $x->update($d); return response()->json($this->query()->findOrFail($x->id)); }
    public function destroy($id) { SubscriptionChange::findOrFail($id)->delete(); return response()->json(['message'=>'Subscription change deleted successfully.']); }
}
