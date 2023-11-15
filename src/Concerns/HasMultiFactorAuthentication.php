<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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

    public function hasMultiFactorEnabled(?Channel $channel = null): Collection|bool
    {
        if ($channel === null) {
            // return enabled channels
            return $this->multiFactors()->whereNotNull('enabled_at')->pluck('channel');
        }

        if (! in_array($channel, self::getAvailableFactors())) {
            return false;
        }

        $enabledAt = $this->multiFactors()->where('channel', $channel->value)->value('enabled_at');

        return $enabledAt !== null;
    }

    public function hasMultiFactorVerified(?Channel $channel = null): Collection|bool
    {
        if ($channel === null) {
            $verifiedChannels = [];
            foreach (self::getAvailableFactors() as $channel) {
                $isVerified = $this->hasMultiFactorVerified($channel);
                if ($isVerified) $verifiedChannels[] = $channel;
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

    public function configureRedirectRoute(string $route, Closure $beforeRedirect = null): void
    {
        $this->redirectRoute = $route;
        $this->beforeRedirect = $beforeRedirect;
    }

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
