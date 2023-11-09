<?php

namespace Kohaku1907\LaraMfa\Models\Strategies;

use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication;

class TotpAuthentication implements AuthenticationStrategy
{
    private MultiFactorAuthentication $mfa;

    private $seconds = 30;

    private $digits = 6;

    public function __construct(MultiFactorAuthentication $mfa)
    {
        $this->mfa = $mfa;
    }

    public function verifyCode(string $code): bool
    {
        $offset = config('mfa.totp.offset');

        for ($i = 0; $i <= $offset; $i++) {
            $time = time() - ($i * $this->seconds);
            $generatedCode = $this->generateCodeFromTime($time);
            if (hash_equals($generatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates a TOTP code based on the secret and time step.
     *
     * @return string The generated TOTP code.
     */
    public function generateCode(): string
    {
        return $this->generateCodeFromTime();
    }

    protected function generateCodeFromTime(int $at = time()): string
    {
        $secret = $this->mfa->secret;
        $time = floor($at / $this->seconds); // TOTP time step is 30 seconds
        $binaryTime = pack('N*', 0).pack('N*', $time); // Convert timestamp to binary
        $hash = hash_hmac('sha1', $binaryTime, $secret, true); // Calculate HMAC-SHA1 hash
        $offset = ord(substr($hash, -1)) & 0x0F; // Calculate offset
        $code = (
            (ord(substr($hash, $offset + 0)) & 0x7F) << 24 |
            (ord(substr($hash, $offset + 1)) & 0xFF) << 16 |
            (ord(substr($hash, $offset + 2)) & 0xFF) << 8 |
            (ord(substr($hash, $offset + 3)) & 0xFF)
        ) % pow(10, $this->digits); // Calculate code

        return str_pad($code, $this->digits, '0', STR_PAD_LEFT); // Pad code with leading zeros
    }
}
