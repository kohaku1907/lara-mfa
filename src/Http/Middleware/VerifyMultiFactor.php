<?php

namespace Kohaku1907\LaraMFA\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Contracts\MultiFactorAuthenticatable;

class VerifyMultiFactor
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if(! $user instanceof MultiFactorAuthenticatable) {
            return $next($request);
        }

        $valid = true;
        foreach (Channel::cases() as $channel) {
            if($user->hasMultiFactorEnabled($channel) && !$this->recentlyConfirmed($request, $channel)) {
                $valid = false;
                break;
            }
        }

        if(!$valid) {
            // check if config redirect route is set else throw an exception unauthorized
            if (config('your_config_key')) {
                // Redirect to the configured route
            } else {
                throw new \Exception('Unauthorized');
            }
        }

        return $next($request);
    }

    protected function recentlyConfirmed(Request $request, $channel): bool
    {
        return $request->session()->get("mfa.{$channel}.expired_at") >= now()->getTimestamp();
    }
}