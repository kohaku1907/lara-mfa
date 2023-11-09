<?php

namespace Kohaku1907\LaraMfa\Models\Strategies;

use App\Models\MultiFactorAuthentication;

class TotpAuthentication implements AuthenticationStrategy
{
    private MultiFactorAuthentication $mfa;

    public function __construct(MultiFactorAuthentication $mfa)
    {
        $this->mfa = $mfa;
    }

    public function verifyCode(string $code): bool
    {
        // Generate a hash value from the secret key
        $hash = hash_hmac('sha1', $this->mfa->secret, true);
        
        // Convert the hash value to an OTP
        $otp = str_pad((int) base_convert($hash, 16, 10) % 1000000, 6, '0', STR_PAD_LEFT);
        
        // Compare the OTP with the provided code
        return $otp === $code;
    }

    /**
     * Generates a TOTP code based on the secret and time step.
     *
     * @return string The generated TOTP code.
     */
    public function generateCode(): string
    {
        $secret = $this->mfa->secret;
        $time = floor(time() / 30); // TOTP time step is 30 seconds
        $binaryTime = pack('N*', 0) . pack('N*', $time); // Convert timestamp to binary
        $hash = hash_hmac('sha1', $binaryTime, $secret, true); // Calculate HMAC-SHA1 hash
        $offset = ord(substr($hash, -1)) & 0x0F; // Calculate offset
        $code = (
            (ord(substr($hash, $offset + 0)) & 0x7F) << 24 |
            (ord(substr($hash, $offset + 1)) & 0xFF) << 16 |
            (ord(substr($hash, $offset + 2)) & 0xFF) << 8 |
            (ord(substr($hash, $offset + 3)) & 0xFF)
        ) % pow(10, $this->mfa->digits); // Calculate code
        return str_pad($code, $this->mfa->digits, '0', STR_PAD_LEFT); // Pad code with leading zeros
    }
}
