<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrivacyPolicyAccepted
{
    /**
     * @var list<string>
     */
    protected array $except = [
        'privacy.show',
        'privacy.edit',
        'profile.edit',
        'notifications.edit',
        'user-data.export',
        'logout',
        'login',
        'password.*',
        'two-factor.*',
        'verification.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasAcceptedPrivacyPolicy()) {
            return $next($request);
        }

        foreach ($this->except as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        return redirect()->route('privacy.edit');
    }
}
