<?php

namespace Kohaku1907\LaraMFA\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kohaku1907\LaraMfa\Contracts\MultiFactorAuthenticatable;
use Kohaku1907\LaraMfa\Enums\Channel;

class ForceVerifyMultiFactor
{
    public function handle(Request $request, Closure $next, ...$channels)
    {
        $user = $request->user();

        if (! $user instanceof MultiFactorAuthenticatable) {
            return $next($request);
        }

        foreach ($channels as $channel) {
            if (! in_array($channel, Channel::cases())) {
                throw new \Exception('Invalid MFA channel: '.$channel);
            }

            if (! $user->hasMultiFactorEnabled($channel) || ! $this->recentlyConfirmed($request, $channel)) {
                if ($user->getMfaRedirectRoute()) {
                    return redirect()->route($user->getMfaRedirectRoute());
                } else {
                    throw new \Exception('Unauthorized');
                }
            }
        }

        return $next($request);
    }

    protected function recentlyConfirmed(Request $request, $channel): bool
    {
        return $request->session()->get("mfa.{$channel}.expired_at") >= now()->getTimestamp();
    }
}
