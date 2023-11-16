<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication as MFAuth;

trait HasSmsFactorAuth
{
    /**
     * Retrieves the SMS factor for the authenticatable model.
     */
    public function smsFactor(): MorphOne
    {
        return $this->morphOne(MFAuth::class, 'authenticatable')->where('channel', Channel::Sms)
            ->withDefault([
                'channel' => Channel::Sms,
                'enabled_at' => null,
                'secret' => null,
            ]);
    }

    /**
     * Creates an SMS factor authentication.
     *
     * @return MFAuth the created SMS factor authentication
     */
    public function createSmsFactorAuth(): MFAuth
    {
        if ($this->smsFactor->exists === false) {
            $this->smsFactor->save();
        }

        return $this->smsFactor;
    }
}
