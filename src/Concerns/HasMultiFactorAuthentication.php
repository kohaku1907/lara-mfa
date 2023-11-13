<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Closure;
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

    public function getAvailableFactors(): array
    {
        $availableFactors = [];

        if (in_array(HasSmsFactorAuth::class, class_uses(static::class))) {
            $availableFactors[] = Channel::Sms->value;
        }

        if (in_array(HasEmailFactorAuth::class, class_uses(static::class))) {
            $availableFactors[] = Channel::Email->value;
        }

        if (in_array(HasTotpAuth::class, class_uses(static::class))) {
            $availableFactors[] = Channel::Totp->value;
        }

        return $availableFactors;
    }

    public function hasMultiFactorEnabled(string $channel = null): bool
    {
        if ($channel === null) {
            return $this->multiFactors()->whereNotNull('enabled_at')->exists();
        }

        if (! in_array($channel, $this->getAvailableFactors())) {
            return false;
        }

        $enabledAt = $this->multiFactors()->where('channel', $channel)->value('enabled_at');

        return $enabledAt !== null;
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
