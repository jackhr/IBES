<?php

namespace App\Http\Middleware;

use App\Models\AdminApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdminToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $this->extractToken($request);

        if ($plainToken === '') {
            return response()->json([
                'success' => false,
                'message' => 'Authentication token is missing.',
            ], 401);
        }

        $token = AdminApiToken::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->where('expires_at', '>', now('UTC'))
            ->with('adminUser')
            ->first();

        if ($token === null || $token->adminUser === null || ! $token->adminUser->active) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed.',
            ], 401);
        }

        $token->forceFill([
            'last_used_at' => now(),
        ])->save();

        $request->attributes->set('adminUser', $token->adminUser);
        $request->attributes->set('adminToken', $token);

        return $next($request);
    }

    private function extractToken(Request $request): string
    {
        $bearer = trim((string) $request->bearerToken());

        if ($bearer !== '') {
            return $bearer;
        }

        return trim((string) $request->header('X-Admin-Token', ''));
    }
}
