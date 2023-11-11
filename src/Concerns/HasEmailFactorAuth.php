<?php

namespace Kohaku1907\LaraMfa\Concerns;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication as MFAuth;

trait HasEmailFactorAuth
{
    use BaseFactorAuth;
    
    public function emailFactor(): MorphOne
    {
        return $this->morphOne(MFAuth::class, 'authenticatable')->where('channel', Channel::Email)
            ->withDefault([
                'channel' => Channel::Email,
                'enabled_at' => null,
                'secret' => null,
            ]);
    }

    public function enableEmailMFAuth(string $code): bool
    {
        return $this->enableMFAuth($this->emailFactor, $code);
    }

    public function disableEmailMFAuth(string $code): bool
    {
        return $this->disableMFAuth($this->emailFactor, $code);
    }

    public function createEmailMFAuth(): MFAuth
    {
        return $this->createMFAuth($this->emailFactor);
    }
}