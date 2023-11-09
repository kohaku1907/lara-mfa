<?php

namespace Kohaku1907\LaraMfa\Models\Strategies;

use App\Models\MultiFactorAuthentication;

class EmailAuthentication implements AuthenticationStrategy
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
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        $length = 6;
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }
}
