<?php

namespace Kohaku1907\LaraMFA\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kohaku1907\LaraMfa\Contracts\MultiFactorAuthenticatable;
use Kohaku1907\LaraMfa\Enums\Channel;

class VerifyMultiFactor
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user instanceof MultiFactorAuthenticatable) {
            return $next($request);
        }

        $valid = true;
        foreach (Channel::cases() as $channel) {
            $channel = $channel->value;
            if ($user->hasMultiFactorEnabled($channel) && ! $this->recentlyConfirmed($request, $channel)) {
                $valid = false;
                break;
            }
        }

        if (! $valid) {
            return $user->multiFactorAuthRedirect();
        }

        return $next($request);
    }

    protected function recentlyConfirmed(Request $request,string $channel): bool
    {
        return $request->session()->get("mfa.{$channel}.expired_at") >= now()->getTimestamp();
    }
}
