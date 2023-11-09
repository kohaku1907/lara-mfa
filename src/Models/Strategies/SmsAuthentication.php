<?php

namespace Kohaku1907\LaraMfa\Models\Strategies;

use App\Models\MultiFactorAuthentication;

class SmsAuthentication implements AuthenticationStrategy
{
    private MultiFactorAuthentication $mfa;

    public function __construct(MultiFactorAuthentication $mfa)
    {
        $this->mfa = $mfa;
    }

    public function verifyCode(string $code): bool
    {
        $storedCode = $this->mfa->getCode();

        return $storedCode === $code;
    }

    public function generateCode(): string
    {
        $code = random_int(100000, 999999);
        return strval($code);
    }
}
