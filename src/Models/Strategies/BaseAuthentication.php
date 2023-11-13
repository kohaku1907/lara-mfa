<?php

namespace Kohaku1907\LaraMfa\Models\Strategies;

use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication;

abstract class BaseAuthentication
{
    protected MultiFactorAuthentication $mfa;

    public function __construct(MultiFactorAuthentication $mfa)
    {
        $this->mfa = $mfa;
    }

    public function enable($code): bool
    {
        if ($this->mfa->isDisabled() && $this->mfa->verifyCode($code)) {
            $this->mfa->update([
                'enabled_at' => now(),
            ]);

            return true;
        }

        return false;
    }

    public function disable($code): bool
    {
        if ($this->mfa->isEnabled() && $this->mfa->verifyCode($code)) {
            $this->mfa->update([
                'enabled_at' => null,
            ]);

            return true;
        }

        return false;
    }
}
