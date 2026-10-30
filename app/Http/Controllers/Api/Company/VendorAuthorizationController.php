<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyUser;
use App\Models\VendorRole;
use App\Models\VendorUserRole;
use App\Models\VendorAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VendorAuthorizationController extends Controller
{
    /**
     * List active roles for the authenticated company.
     */
    public function roles(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => VendorRole::where('company_id', $user->company_id)
                ->where('is_active', true)
                ->orderBy('role_name')
                ->get([
                    'id',
                    'role_code',
                    'role_name',
                    'description',
                    'is_system',
                ]),
        ]);
    }

    /**
     * List active permissions.
     */
    public function permissions(Request $request)
    {
        return response()->json([
            'data' => DB::table('vendor_permissions')
                ->where('is_active', true)
                ->orderBy('module')
                ->orderBy('permission_code')
                ->get([
                    'id',
                    'permission_code',
                    'permission_name',
                    'module',
                    'action',
                    'description',
                ]),
        ]);
    }

    /**
     * List active role assignments for a company user.
     */
    public function userRoles(Request $request, int $userId)
    {
        $actor = $request->user();

        $targetUser = CompanyUser::where('company_id', $actor->company_id)
            ->where('status', 'ACTIVE')
            ->findOrFail($userId);

        $assignments = VendorUserRole::with([
                'role:id,role_code,role_name',
            ])
            ->where('company_id', $actor->company_id)
            ->where('company_user_id', $targetUser->id)
            ->whereHas('role', function ($query) use ($actor) {
                $query->where('company_id', $actor->company_id)
                    ->where('is_active', true);
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get();

        return response()->json([
            'data' => $assignments,
        ]);
    }

    /**
     * Assign or renew a role.
     * Only an active company OWNER can perform this action.
     */
    public function assignRole(Request $request, int $userId)
    {
        $actor = $request->user();

        $this->ensureOwner($actor);

        $validated = $request->validate([
            'role_id' => ['required', 'integer'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        return DB::transaction(function () use (
            $actor,
            $userId,
            $validated,
            $request
        ) {
            $targetUser = CompanyUser::where('company_id', $actor->company_id)
                ->lockForUpdate()
                ->findOrFail($userId);

            if ($targetUser->status !== 'ACTIVE') {
                throw ValidationException::withMessages([
                    'user_id' => 'Roles cannot be assigned to inactive users.',
                ]);
            }

            $role = VendorRole::where('company_id', $actor->company_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->findOrFail($validated['role_id']);

            // Do not allow an Owner to promote another account to OWNER.
            if (
                $role->role_code === 'OWNER'
                && $targetUser->id !== $actor->id
            ) {
                throw ValidationException::withMessages([
                    'role_id' => 'The OWNER role cannot be assigned to another user.',
                ]);
            }

            // Creates a new assignment or renews an existing expired one.
            $assignment = VendorUserRole::updateOrCreate(
                [
                    'company_id' => $actor->company_id,
                    'company_user_id' => $targetUser->id,
                    'role_id' => $role->id,
                ],
                [
                    'assigned_by' => $actor->id,
                    'expires_at' => $validated['expires_at'] ?? null,
                ]
            );

            VendorAuditLog::create([
                'company_id' => $actor->company_id,
                'actor_user_id' => $actor->id,
                'action' => 'vendor.role.assigned',
                'entity_type' => 'company_user',
                'entity_id' => (string) $targetUser->id,
                'details' => [
                    'role_id' => $role->id,
                    'role_code' => $role->role_code,
                    'assignment_id' => $assignment->id,
                    'expires_at' => $assignment->expires_at,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            return response()->json([
                'message' => 'Role assignment saved successfully.',
                'data' => $assignment,
            ], 200);
        });
    }

    /**
     * Revoke a role assignment.
     * The OWNER role cannot be revoked through this API.
     */
    public function revokeRole(
        Request $request,
        int $userId,
        int $roleId
    ) {
        $actor = $request->user();

        $this->ensureOwner($actor);

        return DB::transaction(function () use (
            $actor,
            $userId,
            $roleId,
            $request
        ) {
            $targetUser = CompanyUser::where('company_id', $actor->company_id)
                ->findOrFail($userId);

            $role = VendorRole::where('company_id', $actor->company_id)
                ->findOrFail($roleId);

            if ($role->role_code === 'OWNER') {
                throw ValidationException::withMessages([
                    'role_id' => 'The OWNER role cannot be revoked through this API.',
                ]);
            }

            $assignment = VendorUserRole::where('company_id', $actor->company_id)
                ->where('company_user_id', $targetUser->id)
                ->where('role_id', $role->id)
                ->lockForUpdate()
                ->firstOrFail();

            $assignmentId = $assignment->id;

            $assignment->delete();

            VendorAuditLog::create([
                'company_id' => $actor->company_id,
                'actor_user_id' => $actor->id,
                'action' => 'vendor.role.revoked',
                'entity_type' => 'company_user',
                'entity_id' => (string) $targetUser->id,
                'details' => [
                    'role_id' => $role->id,
                    'role_code' => $role->role_code,
                    'assignment_id' => $assignmentId,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            return response()->json([
                'message' => 'Role revoked successfully.',
            ]);
        });
    }

    /**
     * Verify that the authenticated user has an active OWNER role.
     */
    private function ensureOwner(CompanyUser $user): void
    {
        if ($user->status !== 'ACTIVE') {
            abort(403, 'Inactive users cannot manage roles.');
        }

        $isOwner = DB::table('vendor_user_roles as vur')
            ->join('vendor_roles as vr', 'vr.id', '=', 'vur.role_id')
            ->where('vur.company_id', $user->company_id)
            ->where('vur.company_user_id', $user->id)
            ->where('vr.company_id', $user->company_id)
            ->where('vr.role_code', 'OWNER')
            ->where('vr.is_active', true)
            ->where(function ($query) {
                $query->whereNull('vur.expires_at')
                    ->orWhere('vur.expires_at', '>', now());
            })
            ->exists();

        if (! $isOwner) {
            abort(403, 'Only the company owner can manage role assignments.');
        }
    }
}
