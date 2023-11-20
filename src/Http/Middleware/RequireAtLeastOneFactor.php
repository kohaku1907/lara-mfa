<?php

namespace Kohaku1907\LaraMfa\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kohaku1907\LaraMfa\Contracts\MultiFactorAuthenticatable;
use Kohaku1907\LaraMfa\Enums\Channel;

class RequireAtLeastOneFactor
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user instanceof MultiFactorAuthenticatable) {
            throw new \Exception('User is not multi-factor authenticatable');
        }

        foreach (Channel::cases() as $channel) {
            if ($user->hasMultiFactorEnabled($channel) && $this->recentlyConfirmed($request, $channel->value)) {
                return $next($request);
            }
        }

        // If the user hasn't enabled and verified at least one of the required channels, redirect them to the MFA setup page
        return $user->multiFactorAuthRedirect($user::MIDDLEWARE_REQUIRE_AT_LEAST_ONE_FACTOR);
    }

    protected function recentlyConfirmed(Request $request, string $channel): bool
    {
        return $request->session()->get("mfa.{$channel}.expired_at") >= now()->getTimestamp();
    }
}
