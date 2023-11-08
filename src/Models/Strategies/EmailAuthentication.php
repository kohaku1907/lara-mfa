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
        // Implement verification logic here
    }

    public function generateCode(): string
    {
        // Implement code generation logic here
    }

    public function sendCode(): void
    {
        // Implement email sending logic here
    }
}
