<?php

namespace App\Http\Middleware;

use App\Models\CompanyUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorPermission
{
    /**
     * Check whether the authenticated vendor user has a permission.
     *
     * Usage: ->middleware('vendor.permission:products.view')
     */
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $user = $request->user();

        // Vendor permissions apply only to company users.
        if (! $user instanceof CompanyUser) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        // The vendor user and company must both be active.
        $company = $user->company;

        if (
            $user->status !== 'ACTIVE' ||
            ! $company ||
            $company->status !== 'ACTIVE'
        ) {
            return response()->json([
                'message' => 'Your account or company is inactive.',
            ], 403);
        }

        // Deny access if the permission is missing or inactive.
        $permissionId = DB::table('vendor_permissions')
            ->where('permission_code', $permission)
            ->where('is_active', true)
            ->value('id');

        if (! $permissionId) {
            return response()->json([
                'message' => 'Permission denied.',
            ], 403);
        }

        // Require an active role assigned to this user
        // within the user's own company, with the permission.
        $hasPermission = DB::table('vendor_user_roles as vur')
            ->join('vendor_roles as vr', 'vr.id', '=', 'vur.role_id')
            ->join(
                'vendor_role_permissions as vrp',
                'vrp.role_id',
                '=',
                'vr.id'
            )
            ->where('vur.company_id', $user->company_id)
            ->where('vur.company_user_id', $user->id)
            ->where('vr.company_id', $user->company_id)
            ->where('vrp.permission_id', $permissionId)
            ->where('vr.is_active', true)
            ->where(function ($query) {
                $query->whereNull('vur.expires_at')
                    ->orWhere('vur.expires_at', '>', now());
            })
            ->exists();

        if (! $hasPermission) {
            return response()->json([
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
