<?php

namespace Kohaku1907\LaraMfa\Concerns;

use App\Models\MultiFactorAuthentication;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Kohaku1907\LaraMfa\Concerns\Authenticators\EmailAuthenticator;
use Kohaku1907\LaraMfa\Concerns\Authenticators\MFAuthenticator;
use Kohaku1907\LaraMfa\Concerns\Authenticators\SmsAuthenticator;
use Kohaku1907\LaraMfa\Concerns\Authenticators\TotpAuthenticator;
use Kohaku1907\LaraMfa\Enums\Channel;

trait HasMultiFactorAuthentication
{
    public function multiFactors(): MorphToMany
    {
        return $this->morphToMany(MultiFactorAuthentication::class, 'authenticatable');
    }

    public function enableSmsMFAuth(string $code): void
    {
        $this->enableMFAuth(new SmsAuthenticator($code));
    }

    public function enableEmailMFAuth(string $code): void
    {
        $this->enableMFAuth(new EmailAuthenticator($code));
    }

    public function enableTotpMFAuth(string $code): void
    {
        $this->enableMFAuth(new TotpAuthenticator($code));
    }

    protected function enableMFAuth(MFAuthenticator $authenticator): void
    {
        if ($authenticator->verify()) {
            $this->multiFactors()->updateOrCreate(
                ['channel' => $authenticator->getChannel()],
                [
                    'channel' => $authenticator->getChannel(),
                    'enabled_at' => now(),
                    'secret' => method_exists($authenticator, 'getSecret') ? $authenticator->getSecret() : null,
                    'digits' => method_exists($authenticator, 'getDigits') ? $authenticator->getDigits() : null,
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
