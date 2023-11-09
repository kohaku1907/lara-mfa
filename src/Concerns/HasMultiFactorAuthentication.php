<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication as MFAuth;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Kohaku1907\LaraMfa\Enums\Channel;

trait HasMultiFactorAuthentication
{
    protected function multiFactors(): MorphToMany
    {
        return $this->morphToMany(MFAuth::class, 'authenticatable');
    }

    protected function emailFactor(): MorphOne
    {
        return $this->morphOne(MFAuth::class, 'authenticatable')->where('channel', Channel::Email)
            ->withDefault([
                'channel' => Channel::Email,
                'enabled_at' => null,
                'secret' => null,
            ]);
    }

    protected function smsFactor(): MorphOne
    {
        return $this->morphOne(MFAuth::class, 'authenticatable')->where('channel', Channel::Sms)
            ->withDefault([
                'channel' => Channel::Sms,
                'enabled_at' => null,
                'secret' => null,
            ]);
    }

    protected function totpFactor(): MorphOne
    {
        return $this->morphOne(MFAuth::class, 'authenticatable')->where('channel', Channel::Totp)
            ->withDefault([
                'channel' => Channel::Totp,
                'enabled_at' => null,
                'secret' => null,
            ]);
    }

    public function enableSmsMFAuth(string $code): void
    {
        $this->enableMFAuth($this->smsFactor, $code);
    }

    public function enableEmailMFAuth(string $code): void
    {
        $this->enableMFAuth($this->emailFactor, $code);
    }

    public function enableTotpMFAuth(string $code): void
    {
        $this->enableMFAuth($this->totpFactor, $code);
    }

    protected function enableMFAuth(MFAuth $mfAuth, $code): void
    {
        if ($mfAuth->verifyCode($code)) {
            $this->multiFactors()->updateOrCreate(
                ['channel' => $mfAuth->channel],
                [
                    'channel' => $mfAuth->channel,
                    'enabled_at' => now(),
                ]
            );
        } else {
            throw new \Exception('Invalid verification code');
        }
    }

    public function hasMultiFactorEnabled(Channel $channel): bool
    {
        $enabledAt = $this->multiFactors()->where('channel', $channel)->value('enabled_at');

        return $enabledAt !== null;
    }

     /**
     * Creates a new Multi Factor Auth mechanisms from scratch, and returns a new Secret in case Totp.
     * in case SMS or Email, it will send a code to the user.
     *
     * 
     */
    protected function createMultiFactorAuth(MFAuth $mfAuth): mixed
    {
        // check if  $mfAuth not exist in database create it and  associate with user
        if ($mfAuth->exists === false) {
            if ($mfAuth->channel === Channel::Totp) {
                $mfAuth->secret = $mfAuth::generateRandomSecret();
            }
            $mfAuth->save();
            $this->multiFactors()->save($mfAuth);
        }

        if(in_array($mfAuth->channel, [Channel::Sms, Channel::Email])) {
            $mfAuth->generateCode();
            $mfAuth->sendCode();
        }

        return $mfAuth;
    }

    public function createSmsMFAuth(): MFAuth
    {
        return $this->createMFAuth($this->smsFactor);
    }

    public function createEmailMFAuth(): MFAuth
    {
        return $this->createMFAuth($this->emailFactor);
    }

    public function createTotpMFAuth(): MFAuth
    {
        return $this->createMFAuth($this->totpFactor);
    }

    
}
