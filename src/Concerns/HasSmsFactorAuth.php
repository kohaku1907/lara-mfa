<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication as MFAuth;

trait HasSmsFactorAuth
{
    use BaseFactorAuth;
    
    public function smsFactor(): MorphOne
    {
        return $this->morphOne(MFAuth::class, 'authenticatable')->where('channel', Channel::Sms)
            ->withDefault([
                'channel' => Channel::Sms,
                'enabled_at' => null,
                'secret' => null,
            ]);
    }

    public function enableSmsMFAuth(string $code): bool
    {
        return $this->enableMFAuth($this->smsFactor, $code);
    }

    public function disableSmsMFAuth(string $code): bool
    {
        return $this->disableMFAuth($this->smsFactor, $code);
    }

    public function createSmsMFAuth(): MFAuth
    {
        return $this->createMFAuth($this->smsFactor);
    }
}