<?php

namespace Kohaku1907\LaraMfa\Concerns;

use App\Models\MultiFactorAuthentication as MFAuth;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Kohaku1907\LaraMfa\Enums\Channel;

trait HasMultiFactorAuthentication
{
    public function multiFactors(): MorphToMany
    {
        return $this->morphToMany(MFAuth::class, 'authenticatable');
    }

    public function emailFactor(): MorphOne
    {
        return $this->morphOne(MFAuth::class, 'authenticatable')->where('channel', Channel::Email)
                ->withDefault([
                    'channel' => Channel::Email,
                    'enabled_at' => null,
                    'secret' => null,
                    'digits' => null,
                ]);
    }

    public function smsFactor(): MorphOne
    {
        return $this->morphOne(MFAuth::class, 'authenticatable')->where('channel', Channel::Sms)
                ->withDefault([
                    'channel' => Channel::Sms,
                    'enabled_at' => null,
                    'secret' => null,
                    'digits' => null,
                ]);
    }

    public function totpFactor(): MorphOne
    {
        return $this->morphOne(MFAuth::class, 'authenticatable')->where('channel', Channel::Totp)
                ->withDefault([
                    'channel' => Channel::Totp,
                    'enabled_at' => null,
                    'secret' => null,
                    'digits' => null,
                    'window' => null,
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
                    'secret' => $mfAuth->secret,
                    'digits' => $mfAuth->digits,
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
}
