<?php

namespace Kohaku1907\LaraMfa\Models\Strategies;

interface AuthenticationStrategy
{
    /**
     * Verify the given code.
     *
     * @param  string  $code The code to verify.
     * @return bool True if the code is valid, false otherwise.
     */
    public function verifyCode(string $code): bool;

    /**
     * Generate a new code.
     *
     * @return string The generated code.
     */
    public function generateCode(): string;

    public function enable($code): bool;

    public function disable($code): bool;
}
