<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication as MFAuth;

trait BaseFactorAuth
{
    public function baseMultiFactors(): MorphMany
    {
        return $this->morphMany(related: MFAuth::class, name: 'authenticatable');
    }

    /**
     * Creates a new Multi Factor Auth mechanisms from scratch, and returns a new Secret in case Totp.
     * in case SMS or Email, it will send a code to the user.
     */
    public function createMFAuth(MFAuth $mfAuth): mixed
    {
        // check if  $mfAuth not exist in database create it and  associate with user
        if ($mfAuth->exists === false) {
            $mfAuth->save();
            $this->baseMultiFactors()->save($mfAuth);
        }

        if (in_array($mfAuth->channel, [Channel::Sms, Channel::Email])) {
            $mfAuth->generateCode();
            $mfAuth->sendCode();
        }

        return $mfAuth;
    }
}
