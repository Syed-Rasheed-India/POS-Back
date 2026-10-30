<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CompanyAuthController extends Controller
{
    
public function login(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'username' => ['required', 'string', 'max:100'],
        'password' => ['required', 'string', 'max:255'],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422);
    }

    $username = trim((string) $request->input('username'));
    $password = (string) $request->input('password');

    $user = CompanyUser::query()
        ->where('username', $username)
        ->first();

    if (!$user) {
        return response()->json([
            'message' => 'Invalid username or password.',
        ], 401);
    }

    // Reject an account while its lockout period is active.
    if ($user->locked_until !== null && $user->locked_until->isFuture()) {
        return response()->json([
            'message' => 'This account is temporarily locked.',
        ], 423);
    }

    // A previous lock has expired: start a fresh attempt window.
    if ($user->locked_until !== null) {
        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();
    }

    if (!Hash::check($password, $user->password_hash)) {
        $failedAttempts = (int) $user->failed_login_attempts + 1;

        $updates = [
            'failed_login_attempts' => $failedAttempts,
        ];

        if ($failedAttempts >= 5) {
            $updates['locked_until'] = now()->addMinutes(15);
        }

        try {
            $user->forceFill($updates)->save();
        } catch (\Throwable $exception) {
            Log::error('Failed to record company login attempt.', [
                'company_user_id' => $user->id,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to complete login. Please try again.',
            ], 500);
        }

        if ($failedAttempts >= 5) {
            return response()->json([
                'message' => 'This account is temporarily locked.',
            ], 423);
        }

        return response()->json([
            'message' => 'Invalid username or password.',
        ], 401);
    }

    // Reject inactive company-user accounts.
    if (strtoupper((string) $user->status) !== 'ACTIVE') {
        return response()->json([
            'message' => 'This company account is inactive.',
        ], 403);
    }

    // The Company model's SoftDeletes scope excludes deleted companies.
    $company = $user->company()->first();

    if (!$company || strtoupper((string) $company->status) !== 'ACTIVE') {
        return response()->json([
            'message' => 'This company account is inactive or unavailable.',
        ], 403);
    }

    try {
        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ])->save();

        $token = $user->createToken('retailpos-company')->plainTextToken;
    } catch (\Throwable $exception) {
        Log::error('Company login failed internally.', [
            'company_user_id' => $user->id,
            'exception' => $exception->getMessage(),
        ]);

        return response()->json([
            'message' => 'Unable to complete login. Please try again.',
        ], 500);
    }

    return response()->json([
        'message' => 'Login successful.',
        'token' => $token,
        'token_type' => 'Bearer',
        'user' => [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'company_id' => $user->company_id,
            'username' => $user->username,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'status' => $user->status,
        ],
    ]);
}


    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof CompanyUser) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $token = $user->currentAccessToken();

        if ($token === null || !method_exists($token, 'delete')) {
            return response()->json([
                'message' => 'No revocable access token was found.',
            ], 400);
        }

        $token->delete();

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }
}
