<?php

namespace Kohaku1907\LaraMfa\Models\Strategies;

interface AuthenticationStrategy
{
    public function verifyCode(string $code): bool;

    public function generateCode(): string;

    public function sendCode(): void;
}
