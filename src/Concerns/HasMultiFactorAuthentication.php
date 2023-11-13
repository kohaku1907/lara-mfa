<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Routing\Route;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication as MFAuth;

trait HasMultiFactorAuthentication
{
    protected $mfaRedirectRoute;

    public function initializeHasMultiFactorAuthentication()
    {
        $this->validateMfaRedirectRoute();
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

    public function setMfaRedirectRoute(string $route): void
    {
        $this->mfaRedirectRoute = $route;
    }

    public function getMfaRedirectRoute(): ?string
    {
        return $this->mfaRedirectRoute;
    }

    protected function validateMfaRedirectRoute(): void
    {
        if ($this->mfaRedirectRoute !== null && ! Route::has($this->mfaRedirectRoute)) {
            throw new \Exception('Invalid MFA redirect route: '.$this->mfaRedirectRoute);
        }
    }
}
