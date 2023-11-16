<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication as MFAuth;

trait HasEmailFactorAuth
{
    /**
     * Retrieve the email factor authentication for the authenticatable model.
     */
    public function emailFactor(): MorphOne
    {
        return $this->morphOne(MFAuth::class, 'authenticatable')->where('channel', Channel::Email)
            ->withDefault([
                'channel' => Channel::Email,
                'enabled_at' => null,
                'secret' => null,
            ]);
    }

    /**
     * Creates an email factor authentication.
     *
     * @return MFAuth The created email factor authentication.
     */
    public function createEmailFactorAuth(): MFAuth
    {
        if ($this->emailFactor->exists === false) {
            $this->emailFactor->save();

        }

        return $this->emailFactor;
    }
}
