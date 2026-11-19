<?php

namespace App\Http\Middleware;

use App\Models\StaffActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    /**
     * Prevent blocked users and remotely-invalidated browser sessions from
     * accessing authenticated routes.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $isActive = in_array((string) $user->getRawOriginal('status'), ['1', 'active'], true);

        if (! $isActive || $this->sessionWasInvalidated($request, $user) || $this->staffActivityWasLoggedOut($request, $user)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your account is blocked or your session has expired.'], 401);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', ! $isActive
                    ? 'Your account has been blocked. Please contact an administrator.'
                    : 'You have been logged out by an administrator.');
        }

        return $next($request);
    }

    private function sessionWasInvalidated(Request $request, $user): bool
    {
        $invalidatedAt = $user->session_invalidated_at;

        if (! $invalidatedAt || ! $request->hasSession()) {
            return false;
        }

        $startedAt = $request->session()->get('authenticated_at');

        return $startedAt && Carbon::parse($startedAt)->lte($invalidatedAt);
    }

    private function staffActivityWasLoggedOut(Request $request, $user): bool
    {
        $activityLogId = $request->hasSession() ? $request->session()->get('staff_activity_log_id') : null;

        return $activityLogId
            && StaffActivityLog::query()
                ->whereKey($activityLogId)
                ->where('user_id', $user->id)
                ->whereNotNull('logged_out_at')
                ->exists();
    }
}
