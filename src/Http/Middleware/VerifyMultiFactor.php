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
            throw new \Exception('User is not multi-factor authenticatable');
        }

        $valid = true;
        foreach (Channel::cases() as $channel) {
            if ($user->hasMultiFactorEnabled($channel) && ! $this->recentlyConfirmed($request, $channel->value)) {
                $valid = false;
                break;
            }
        }

        if (! $valid) {
            return $user->multiFactorAuthRedirect($user::MIDDLEWARE_VERIFY_MULTI_FACTOR);
        }

        return $next($request);
    }

    protected function recentlyConfirmed(Request $request, string $channel): bool
    {
        return $request->session()->get("mfa.{$channel}.expired_at") >= now()->getTimestamp();
    }
}
