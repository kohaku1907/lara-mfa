<?php

namespace Kohaku1907\LaraMFA\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kohaku1907\LaraMfa\Contracts\MultiFactorAuthenticatable;
use Kohaku1907\LaraMfa\Enums\Channel;

class EnforceMultiFactor
{
    public function handle(Request $request, Closure $next, ...$channels)
    {
        $user = $request->user();

        if (! $user instanceof MultiFactorAuthenticatable) {
            throw new \Exception('User is not multi-factor authenticatable');
        }

        foreach ($channels as $channel) {
            $channel = Channel::from($channel);
            if (! $user->hasMultiFactorEnabled($channel) || ! $this->recentlyConfirmed($request, $channel->value)) {
                return $user->multiFactorAuthRedirect();
            }
        }

        return $next($request);
    }

    protected function recentlyConfirmed(Request $request, string $channel): bool
    {
        return $request->session()->get("mfa.{$channel}.expired_at") >= now()->getTimestamp();
    }
}
