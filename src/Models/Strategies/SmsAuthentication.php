<?php

namespace Kohaku1907\LaraMfa\Models\Strategies;

use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication;

class SmsAuthentication extends BaseAuthentication implements AuthenticationStrategy
{
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
