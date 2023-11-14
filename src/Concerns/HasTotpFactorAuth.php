<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication as MFAuth;

trait HasTotpFactorAuth
{
    public function totpFactor(): MorphOne
    {
        return $this->morphOne(MFAuth::class, 'authenticatable')->where('channel', Channel::Totp)
            ->withDefault([
                'channel' => Channel::Totp,
                'enabled_at' => null,
                'secret' => MFAuth::generateRandomSecret(),
            ]);
    }

    public function createTotpFactorAuth(): MFAuth
    {
        if ($this->totpFactor->exists === false) {
            $this->totpFactor->save();
        }

        if($this->totpFactor->secret === null) {
            $this->totpFactor->secret = MFAuth::generateRandomSecret();
            $this->totpFactor->save();
        }

        return $this->totpFactor;
    }
}
