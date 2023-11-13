<?php

namespace Kohaku1907\LaraMfa\Models\Strategies;

use Illuminate\Support\Facades\Cache;

class TotpAuthentication extends BaseAuthentication implements AuthenticationStrategy
{
    private $seconds = 30;

    private $digits = 6;

    public function verifyCode(string $code): bool
    {
        if ($this->codeHasBeenUsed($code)) {
            return false;
        }

        $periods = config('mfa.totp.offset_periods');

        for ($i = 0; $i <= $periods; $i++) {
            $time = time() - ($i * $this->seconds);
            $generatedCode = $this->generateCodeFromTime($time);
            if (hash_equals($generatedCode, $code)) {
                $this->setCodeAsUsed($code);

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

    /**
     * Returns the cache key string to save the codes into the cache.
     */
    protected function cacheKey(string $code): string
    {
        return implode('|', ['mfa.totp_code', $this->mfa->getKey(), $code]);
    }

    /**
     * Checks if the code has been used.
     */
    protected function codeHasBeenUsed(string $code): bool
    {
        return Cache::has($this->cacheKey($code));
    }

    /**
     * Sets the Code has used, so it can't be used again.
     */
    protected function setCodeAsUsed(string $code): void
    {
        $offset = config('mfa.totp.offset_periods') + 1; // Add 1 more period to the offset
        $periods = (time() / $this->seconds) + $offset;
        $timestamp = (int) $periods * $this->seconds;

        Cache::set($this->cacheKey($code), true, $timestamp);
    }
}
