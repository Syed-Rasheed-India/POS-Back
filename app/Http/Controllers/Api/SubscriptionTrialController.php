<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionTrial;
use Illuminate\Http\Request;

class SubscriptionTrialController extends Controller
{
    public function index() { return response()->json(SubscriptionTrial::with(['company','subscription.plan'])->orderByDesc('id')->get()); }
    public function store(Request $r) { $d=$r->validate(['company_id'=>'required|exists:companies,id','subscription_id'=>'required|exists:company_subscriptions,id','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','status'=>'required|string|max:20','converted_at'=>'nullable|date']); $x=SubscriptionTrial::create($d); return response()->json($x->load(['company','subscription.plan']),201); }
    public function show($id) { return response()->json(SubscriptionTrial::with(['company','subscription.plan'])->findOrFail($id)); }
    public function update(Request $r,$id) { $x=SubscriptionTrial::findOrFail($id); $d=$r->validate(['company_id'=>'sometimes|exists:companies,id','subscription_id'=>'sometimes|exists:company_subscriptions,id','start_date'=>'sometimes|date','end_date'=>'sometimes|date','status'=>'sometimes|string|max:20','converted_at'=>'nullable|date']); $x->update($d); return response()->json($x->fresh(['company','subscription.plan'])); }
    public function destroy($id) { SubscriptionTrial::findOrFail($id)->delete(); return response()->json(['message'=>'Subscription trial deleted successfully.']); }
}
