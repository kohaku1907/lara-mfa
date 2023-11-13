<?php

namespace Kohaku1907\LaraMfa\Models\Strategies;

use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication;

class EmailAuthentication extends BaseAuthentication implements AuthenticationStrategy
{

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
