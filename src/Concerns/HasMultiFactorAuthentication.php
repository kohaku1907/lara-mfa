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
    protected $redirectRoute;

    protected $beforeRedirect;

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
     * @param string $route The route to redirect to.
     * @param Closure|null $beforeRedirect An optional closure to execute before the redirect.
     * @return void
     */
    public function configureRedirectRoute(string $route, Closure $beforeRedirect = null): void
    {
        $this->redirectRoute = $route;
        $this->beforeRedirect = $beforeRedirect;
    }

    /**
     * Redirects the user after multi-factor authentication.
     *
     * @throws Exception If the redirect route is invalid or missing.
     * @return mixed The redirect response.
     */
    public function multiFactorAuthRedirect(): mixed
    {
        if ($this->beforeRedirect) {
            call_user_func($this->beforeRedirect);
        }

        if ($this->redirectRoute) {
            if (Route::has($this->redirectRoute)) {
                return redirect()->route($this->redirectRoute);
            } else {
                return redirect($this->redirectRoute);
            }
        } else {
            abort(401);
        }
    }
}
