<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\CompanyUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only authenticated company users can access vendor routes.
        if (!$user instanceof CompanyUser) {
            return response()->json([
                'message' => 'A company user session is required.',
            ], 403);
        }

        // Reject inactive company-user accounts.
        if (strtoupper((string) $user->status) !== 'ACTIVE') {
            return response()->json([
                'message' => 'This company user account is inactive.',
            ], 403);
        }

        // Resolve the company using the actual database schema.
        $company = Company::query()
            ->whereKey($user->company_id)
            ->first();

        // Soft-deleted companies are excluded by the Company model's
        // SoftDeletes global scope.
        if (!$company || strtoupper((string) $company->status) !== 'ACTIVE') {
            return response()->json([
                'message' => 'This company account is inactive or unavailable.',
            ], 403);
        }

        return $next($request);
    }
}
