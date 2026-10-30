<?php

namespace App\Http\Middleware;

use App\Models\PlatformAdmin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (!$user instanceof PlatformAdmin) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (strtoupper((string) $user->status) !== 'ACTIVE') {
            return response()->json([
                'message' => 'Your administrator account is inactive.',
            ], 403);
        }

        if (!$user->hasSuperAdminRole()) {
            return response()->json([
                'message' => 'Super Admin access is required.',
            ], 403);
        }

        return $next($request);
    }
}
