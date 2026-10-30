<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SuperAdminSecurityController extends Controller
{
    private const SETTINGS = [
        'security.session_timeout_minutes' => ['type' => 'integer', 'min' => 5, 'max' => 1440],
        'security.login_max_attempts' => ['type' => 'integer', 'min' => 3, 'max' => 20],
        'security.lockout_minutes' => ['type' => 'integer', 'min' => 1, 'max' => 1440],
        'security.require_two_factor' => ['type' => 'boolean'],
        'security.event_retention_days' => ['type' => 'integer', 'min' => 7, 'max' => 3650],
    ];

    public function dashboard()
    {
        $eventCount = Schema::hasTable('platform_security_events') ? DB::table('platform_security_events')->count() : 0;
        $unresolved = Schema::hasTable('platform_security_events') ? DB::table('platform_security_events')->whereIn('status', ['OPEN','INVESTIGATING'])->count() : 0;
        $failedLogins = DB::table('platform_admin_login_logs')->whereIn('login_status', ['FAILED','FAILURE'])->where('logged_at', '>=', now()->subHours(24))->count();
        $sessions = DB::table('platform_admin_sessions')->whereNull('revoked_at')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count();
        $recent = Schema::hasTable('platform_security_events') ? DB::table('platform_security_events')->orderByDesc('occurred_at')->limit(5)->get() : collect();
        return response()->json(['metrics' => [
            'total_security_events' => $eventCount, 'unresolved_threats' => $unresolved,
            'failed_logins_24h' => $failedLogins, 'active_admin_sessions' => $sessions,
        ], 'recent_security_events' => $recent]);
    }

    public function events(Request $request)
    {
        $data = $request->validate([
            'severity' => ['nullable', Rule::in(['LOW','MEDIUM','HIGH','CRITICAL'])],
            'status' => ['nullable', Rule::in(['OPEN','INVESTIGATING','RESOLVED','DISMISSED'])],
            'event_code' => 'nullable|string|max:100', 'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        $events = DB::table('platform_security_events')
            ->when($data['severity'] ?? null, fn ($q, $v) => $q->where('severity', $v))
            ->when($data['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($data['event_code'] ?? null, fn ($q, $v) => $q->where('event_code', $v))
            ->orderByDesc('occurred_at')->paginate($data['per_page'] ?? 20);
        return response()->json($events);
    }

    public function showEvent(string $uuid)
    {
        return response()->json(DB::table('platform_security_events')->where('uuid', $uuid)->firstOrFail());
    }

    public function assignEvent(Request $request, string $uuid)
    {
        $event = DB::table('platform_security_events')->where('uuid', $uuid)->firstOrFail();
        $data = $request->validate(['assigned_admin_id' => 'required|integer|exists:platform_admins,id']);
        DB::table('platform_security_events')->where('id', $event->id)->update([
            'assigned_admin_id' => $data['assigned_admin_id'], 'status' => 'INVESTIGATING', 'updated_at' => now(),
        ]);
        $this->audit($request, 'SECURITY_EVENT_ASSIGNED', 'platform_security_events', $event->id, $data);
        return response()->json(['message' => 'Security event assigned.', 'event' => DB::table('platform_security_events')->where('id', $event->id)->first()]);
    }

    public function updateEventStatus(Request $request, string $uuid)
    {
        $event = DB::table('platform_security_events')->where('uuid', $uuid)->firstOrFail();
        $data = $request->validate(['status' => ['required', Rule::in(['OPEN','INVESTIGATING','RESOLVED','DISMISSED'])], 'resolution_notes' => 'nullable|string|max:10000']);
        $updates = ['status' => $data['status'], 'updated_at' => now()];
        if (in_array($data['status'], ['RESOLVED','DISMISSED'], true)) {
            $updates['resolved_at'] = now();
            if (array_key_exists('resolution_notes', $data)) $updates['resolution_notes'] = $data['resolution_notes'];
        } else {
            $updates['resolved_at'] = null;
        }
        DB::table('platform_security_events')->where('id', $event->id)->update($updates);
        $this->audit($request, 'SECURITY_EVENT_STATUS_CHANGED', 'platform_security_events', $event->id, ['from' => $event->status, 'to' => $data['status']]);
        return response()->json(['message' => 'Security event status updated.', 'event' => DB::table('platform_security_events')->where('id', $event->id)->first()]);
    }

    public function resolveEvent(Request $request, string $uuid)
    {
        $event = DB::table('platform_security_events')->where('uuid', $uuid)->firstOrFail();
        $data = $request->validate(['resolution_notes' => 'required|string|max:10000']);
        DB::table('platform_security_events')->where('id', $event->id)->update(['status' => 'RESOLVED', 'resolution_notes' => $data['resolution_notes'], 'resolved_at' => now(), 'updated_at' => now()]);
        $this->audit($request, 'SECURITY_EVENT_RESOLVED', 'platform_security_events', $event->id);
        return response()->json(['message' => 'Security event resolved.', 'event' => DB::table('platform_security_events')->where('id', $event->id)->first()]);
    }

    public function auditLogs(Request $request)
    {
        $data = $request->validate(['action_code' => 'nullable|string|max:100', 'actor_type' => 'nullable|string|max:50', 'per_page' => 'nullable|integer|min:1|max:100']);
        $logs = DB::table('platform_audit_logs')
            ->when($data['action_code'] ?? null, fn ($q, $v) => $q->where('action_code', $v))
            ->when($data['actor_type'] ?? null, fn ($q, $v) => $q->where('actor_type', $v))
            ->orderByDesc('created_at')->paginate($data['per_page'] ?? 25);
        return response()->json($logs);
    }

    public function loginHistory(Request $request)
    {
        $data = $request->validate(['login_status' => ['nullable', Rule::in(['SUCCESS','FAILED','FAILURE'])], 'username' => 'nullable|string|max:100', 'per_page' => 'nullable|integer|min:1|max:100']);
        $logs = DB::table('platform_admin_login_logs')
            ->when($data['login_status'] ?? null, fn ($q, $v) => $q->where('login_status', $v))
            ->when($data['username'] ?? null, fn ($q, $v) => $q->where('username', 'ILIKE', '%'.$v.'%'))
            ->orderByDesc('logged_at')->paginate($data['per_page'] ?? 25);
        return response()->json($logs);
    }

    public function sessions(Request $request)
    {
        $data = $request->validate(['include_revoked' => 'nullable|boolean', 'per_page' => 'nullable|integer|min:1|max:100']);
        $query = DB::table('platform_admin_sessions')
            ->leftJoin('platform_admins', 'platform_admin_sessions.admin_id', '=', 'platform_admins.id')
            ->select('platform_admin_sessions.id','platform_admin_sessions.uuid','platform_admin_sessions.admin_id',
                'platform_admin_sessions.ip_address','platform_admin_sessions.user_agent','platform_admin_sessions.device_info',
                'platform_admin_sessions.expires_at','platform_admin_sessions.last_activity_at','platform_admin_sessions.revoked_at','platform_admin_sessions.created_at',
                'platform_admins.username as admin_username','platform_admins.email as admin_email','platform_admins.first_name as admin_first_name','platform_admins.last_name as admin_last_name');
        if (!($data['include_revoked'] ?? false)) $query->whereNull('platform_admin_sessions.revoked_at');
        $sessions = $query->orderByDesc('platform_admin_sessions.last_activity_at')->paginate($data['per_page'] ?? 25);
        return response()->json($sessions);
    }

    public function revokeSession(Request $request, string $uuid)
    {
        $session = DB::table('platform_admin_sessions')->where('uuid', $uuid)->first();
        if (!$session) return response()->json(['message' => 'Session not found.'], 404);
        if ($session->revoked_at) return response()->json(['message' => 'Session has already been revoked.']);
        // Refuse to claim success unless the session is linked to a server-side
        // Sanctum credential that can actually be invalidated.
        if (!Schema::hasTable('personal_access_tokens') || empty($session->token_hash)) {
            return response()->json(['message' => 'This session is not linked to a revocable Sanctum token. No credential was changed.'], 409);
        }
        $linkedToken = DB::table('personal_access_tokens')->where('token', $session->token_hash)->first();
        if (!$linkedToken) {
            return response()->json(['message' => 'No matching server-side Sanctum token was found for this session. No credential was changed.'], 409);
        }
        DB::transaction(function () use ($session, $linkedToken, $request) {
            DB::table('personal_access_tokens')->where('id', $linkedToken->id)->delete();
            DB::table('platform_admin_sessions')->where('id', $session->id)->update(['revoked_at' => now()]);
            $this->audit($request, 'ADMIN_SESSION_REVOKED', 'platform_admin_sessions', $session->id);
        });
        return response()->json(['message' => 'Session revoked and its server-side Sanctum token invalidated.']);
    }

    public function tokens(Request $request)
    {
        $data = $request->validate(['admin_id' => 'nullable|integer|min:1', 'per_page' => 'nullable|integer|min:1|max:100']);
        // Laravel Sanctum's existing token table is the canonical token store; do not create a duplicate access_tokens table.
        $query = DB::table('personal_access_tokens')->select('id','tokenable_type','tokenable_id','name','abilities','last_used_at','expires_at','created_at','updated_at')
            ->where('tokenable_type', \App\Models\PlatformAdmin::class);
        if (!empty($data['admin_id'])) $query->where('tokenable_id', $data['admin_id']);
        return response()->json($query->orderByDesc('created_at')->paginate($data['per_page'] ?? 25));
    }

    public function revokeToken(Request $request, int $id)
    {
        $token = DB::table('personal_access_tokens')->where('id', $id)->where('tokenable_type', \App\Models\PlatformAdmin::class)->first();
        if (!$token) return response()->json(['message' => 'Administrator token not found.'], 404);
        DB::transaction(function () use ($token, $request) {
            DB::table('personal_access_tokens')->where('id', $token->id)->delete();
            DB::table('platform_admin_sessions')->where('admin_id', $token->tokenable_id)->where('token_hash', $token->token)->whereNull('revoked_at')->update(['revoked_at' => now()]);
            $this->audit($request, 'ADMIN_API_TOKEN_REVOKED', 'personal_access_tokens', $token->id, ['admin_id' => $token->tokenable_id]);
        });
        return response()->json(['message' => 'Administrator API token revoked.']);
    }

    public function twoFactorStatus(Request $request)
    {
        $data = $request->validate(['admin_id' => 'nullable|integer|min:1', 'per_page' => 'nullable|integer|min:1|max:100']);
        $query = DB::table('two_factor_auth')->join('platform_admins', 'two_factor_auth.platform_admin_id', '=', 'platform_admins.id')
            ->select('two_factor_auth.id','two_factor_auth.uuid','two_factor_auth.platform_admin_id','two_factor_auth.method','two_factor_auth.is_enabled','two_factor_auth.confirmed_at','two_factor_auth.last_verified_at','two_factor_auth.created_at','two_factor_auth.updated_at','platform_admins.username','platform_admins.email','platform_admins.first_name','platform_admins.last_name');
        if (!empty($data['admin_id'])) $query->where('two_factor_auth.platform_admin_id', $data['admin_id']);
        return response()->json($query->orderBy('platform_admins.id')->paginate($data['per_page'] ?? 25));
    }

    public function securitySettings()
    {
        $settings = DB::table('platform_settings')->whereIn('setting_key', array_keys(self::SETTINGS))->orderBy('setting_key')->get(['setting_key','setting_value','value_type','description','updated_at']);
        return response()->json(['settings' => $settings]);
    }

    public function updateSecuritySettings(Request $request)
    {
        $data = $request->validate(['settings' => 'required|array|min:1']);
        $errors = [];
        foreach ($data['settings'] as $key => $value) {
            if (!isset(self::SETTINGS[$key])) { $errors["settings.$key"] = ['This security setting is not supported.']; continue; }
            $spec = self::SETTINGS[$key];
            if ($spec['type'] === 'integer' && (!filter_var($value, FILTER_VALIDATE_INT) || $value < $spec['min'] || $value > $spec['max'])) {
                $errors["settings.$key"] = ["Value must be an integer between {$spec['min']} and {$spec['max']}."];
            }
            if ($spec['type'] === 'boolean' && !is_bool($value)) $errors["settings.$key"] = ['Value must be a boolean.'];
        }
        if ($errors) return response()->json(['message' => 'The given data was invalid.', 'errors' => $errors], 422);

        foreach ($data['settings'] as $key => $value) {
            $spec = self::SETTINGS[$key];
            $storedValue = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
            $existing = DB::table('platform_settings')->where('setting_key', $key)->first();
            if ($existing) {
                DB::table('platform_settings')->where('setting_key', $key)->update([
                    'setting_value' => $storedValue, 'value_type' => $spec['type'],
                    'description' => $existing->description ?: 'Security policy setting', 'is_public' => false,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('platform_settings')->insert([
                    'setting_key' => $key, 'setting_value' => $storedValue, 'value_type' => $spec['type'],
                    'description' => 'Security policy setting', 'is_public' => false,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        $this->audit($request, 'SECURITY_SETTINGS_UPDATED', 'platform_settings', 0, ['keys' => array_keys($data['settings'])]);
        return $this->securitySettings();
    }

    private function audit(Request $request, string $action, string $entity, int $entityId, array $metadata = []): void
    {
        if (!Schema::hasTable('platform_audit_logs')) return;
        $requestId = $request->header('X-Request-ID');
        DB::table('platform_audit_logs')->insert([
            'uuid' => (string) Str::uuid(), 'actor_type' => 'PLATFORM_ADMIN', 'platform_admin_id' => $request->user()->id,
            'action_code' => $action, 'entity_type' => $entity, 'entity_id' => $entityId,
            'description' => $action, 'new_values' => json_encode($metadata),
            'request_id' => $requestId && Str::isUuid($requestId) ? $requestId : null,
            'source_ip' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 1000), 'created_at' => now(),
        ]);
    }
}
