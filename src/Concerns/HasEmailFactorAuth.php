<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication as MFAuth;

trait HasEmailFactorAuth
{
    public function emailFactor(): MorphOne
    {
        return $this->morphOne(MFAuth::class, 'authenticatable')->where('channel', Channel::Email)
            ->withDefault([
                'channel' => Channel::Email,
                'enabled_at' => null,
                'secret' => null,
            ]);
    }

    public function createEmailMFAuth(): MFAuth
    {
        if ($this->emailFactor->exists === false) {
            $this->emailFactor->save();
            
        }

        $this->emailFactor->generateCode();
        $this->emailFactor->sendCode();

        return $this->emailFactor;
    }
}
