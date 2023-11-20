<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication as MFAuth;

trait HasMultiFactorAuthentication
{
    const MIDDLEWARE_ENFORCE_MULTI_FACTOR = 'enforceMultiFactor';
    const MIDDLEWARE_REQUIRE_AT_LEAST_ONE_FACTOR = 'requireAtLeastOneFactor';
    const MIDDLEWARE_VERIFY_MULTI_FACTOR = 'verifyMultiFactor';

    protected $enforceMultiFactorRedirectRoute;
    protected $requireAtLeastOneFactorRedirectRoute;
    protected $verifyMultiFactorRedirectRoute;

    protected $enforceMultiFactorBeforeRedirect;
    protected $requireAtLeastOneFactorBeforeRedirect;
    protected $verifyMultiFactorBeforeRedirect;

    public function initializeHasMultiFactorAuthentication()
    {
        $this->registerMultiFactorAuthentication();
    }

    public function multiFactors(): MorphMany
    {
        return $this->morphMany(related: MFAuth::class, name: 'authenticatable');
    }

    /**
     * Returns an array of available factors.
     *
     * @return array
     */
    public static function getAvailableFactors(): array
    {
        $availableFactors = [];

        if (in_array(HasSmsFactorAuth::class, class_uses(static::class))) {
            $availableFactors[] = Channel::Sms;
        }

        if (in_array(HasEmailFactorAuth::class, class_uses(static::class))) {
            $availableFactors[] = Channel::Email;
        }

        if (in_array(HasTotpFactorAuth::class, class_uses(static::class))) {
            $availableFactors[] = Channel::Totp;
        }

        return $availableFactors;
    }

    /**
     * Returns a collection of enabled multi-factor authentication channels or a boolean value
     * indicating if the provided channel is enabled for the user.
     *
     * @param Channel|null $channel The channel for which to check if it is enabled. If null, all enabled channels are returned.
     * @return Collection|bool Returns a collection of enabled channels if $channel is null, or a boolean value indicating if the provided channel is enabled.
     */
    public function hasMultiFactorEnabled(Channel $channel = null): Collection|bool
    {
        if ($channel === null) {
            return $this->multiFactors()->whereNotNull('enabled_at')->pluck('channel');
        }

        if (! in_array($channel, self::getAvailableFactors())) {
            return false;
        }

        $enabledAt = $this->multiFactors()->where('channel', $channel->value)->value('enabled_at');

        return $enabledAt !== null;
    }

    /**
     * Determines if the user has verified multi-factor authentication for a given channel.
     *
     * @param Channel|null $channel The channel for which to check the verification status. If null, checks all available channels.
     * @return Collection|bool Returns a collection of verified channels if $channel is null, or a boolean indicating the verification status for the specified channel.
     */
    public function hasMultiFactorVerified(Channel $channel = null): Collection|bool
    {
        if ($channel === null) {
            $verifiedChannels = [];
            foreach (self::getAvailableFactors() as $channel) {
                $isVerified = $this->hasMultiFactorVerified($channel);
                if ($isVerified) {
                    $verifiedChannels[] = $channel;
                }
            }

            return collect($verifiedChannels);
        }

        if (! in_array($channel, self::getAvailableFactors())) {
            return false;
        }

        return $this->multiFactors()->where('channel', $channel->value)->first()?->isVerified();
    }

    public function registerMultiFactorAuthentication(): void
    {
    }

    /**
     * Configure the redirect route and optional before redirect closure.
     *
     * @param string $middleware The middleware to configure.
     * @param string $route The route to redirect to.
     * @param Closure|null $beforeRedirect An optional closure to execute before the redirect.
     * @return void
     */
    public function configureRedirectRoute(string $middleware, string $route, Closure $beforeRedirect = null): void
    {
        switch ($middleware) {
            case self::MIDDLEWARE_ENFORCE_MULTI_FACTOR:
                $this->enforceMultiFactorRedirectRoute = $route;
                $this->enforceMultiFactorBeforeRedirect = $beforeRedirect;
                break;
            case self::MIDDLEWARE_REQUIRE_AT_LEAST_ONE_FACTOR:
                $this->requireAtLeastOneFactorRedirectRoute = $route;
                $this->requireAtLeastOneFactorBeforeRedirect = $beforeRedirect;
                break;
            case self::MIDDLEWARE_VERIFY_MULTI_FACTOR:
                $this->verifyMultiFactorRedirectRoute = $route;
                $this->verifyMultiFactorBeforeRedirect = $beforeRedirect;
                break;
        }
    }

    /**
     * Redirects the user after multi-factor authentication.
     *
     * @param string $middleware The middleware to use.
     * @throws Exception If the redirect route is invalid or missing.
     * @return mixed The redirect response.
     */
    public function multiFactorAuthRedirect(string $middleware): mixed
    {
        $beforeRedirect = match ($middleware) {
            self::MIDDLEWARE_ENFORCE_MULTI_FACTOR => $this->enforceMultiFactorBeforeRedirect,
            self::MIDDLEWARE_REQUIRE_AT_LEAST_ONE_FACTOR => $this->requireAtLeastOneFactorBeforeRedirect,
            self::MIDDLEWARE_VERIFY_MULTI_FACTOR => $this->verifyMultiFactorBeforeRedirect,
            default => null,
        };

        if ($beforeRedirect) {
            call_user_func($beforeRedirect);
        }

        $redirectRoute = match ($middleware) {
            self::MIDDLEWARE_ENFORCE_MULTI_FACTOR => $this->enforceMultiFactorRedirectRoute,
            self::MIDDLEWARE_REQUIRE_AT_LEAST_ONE_FACTOR => $this->requireAtLeastOneFactorRedirectRoute,
            self::MIDDLEWARE_VERIFY_MULTI_FACTOR => $this->verifyMultiFactorRedirectRoute,
            default => null,
        };

        if ($redirectRoute) {
            if (Route::has($redirectRoute)) {
                return redirect()->route($redirectRoute);
            } else {
                return redirect($redirectRoute);
            }
        } else {
            abort(401);
        }
    }
}
