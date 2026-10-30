<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\CompanyLimitOverride;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyLimitOverrideController extends Controller
{
    private function query() { return CompanyLimitOverride::with(['company','subscription.plan','createdBy'])->orderByDesc('id'); }
    public function index() { return response()->json($this->query()->get()); }
    public function store(Request $r) { $d=$r->validate(['uuid'=>'sometimes|uuid|unique:company_limit_overrides,uuid','company_id'=>'required|exists:companies,id','subscription_id'=>'required|exists:company_subscriptions,id','limit_code'=>'required|string|max:80','base_limit'=>'required|numeric','override_value'=>'required|numeric','override_type'=>'required|string|max:20|in:SET,INCREASE,DECREASE','reason'=>'nullable|string','starts_at'=>'sometimes|date','expires_at'=>'nullable|date','status'=>'required|string|max:20','created_by'=>'required|exists:platform_admins,id']); $d['uuid']??=(string)Str::uuid(); $d['starts_at']??=now(); return response()->json($this->query()->findOrFail(CompanyLimitOverride::create($d)->id),201); }
    public function show($id) { return response()->json($this->query()->findOrFail($id)); }
    public function update(Request $r,$id) { $x=CompanyLimitOverride::findOrFail($id); $d=$r->validate(['uuid'=>'sometimes|uuid|unique:company_limit_overrides,uuid,'.$id,'company_id'=>'sometimes|exists:companies,id','subscription_id'=>'sometimes|exists:company_subscriptions,id','limit_code'=>'sometimes|string|max:80','base_limit'=>'sometimes|numeric','override_value'=>'sometimes|numeric','override_type'=>'sometimes|string|max:20|in:SET,INCREASE,DECREASE','reason'=>'nullable|string','starts_at'=>'sometimes|date','expires_at'=>'nullable|date','status'=>'sometimes|string|max:20','created_by'=>'sometimes|exists:platform_admins,id']); $x->update($d); return response()->json($this->query()->findOrFail($x->id)); }
    public function destroy($id) { CompanyLimitOverride::findOrFail($id)->delete(); return response()->json(['message'=>'Company limit override deleted successfully.']); }
}
