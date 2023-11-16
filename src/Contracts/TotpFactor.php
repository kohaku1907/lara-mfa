<?php

namespace Kohaku1907\LaraMfa\Contracts;

interface TotpFactor
{
    /**
     * Returns the Secret as a QR Code.
     */
    public function toQr(): string;

    /**
     * Returns the Secret as a string.
     */
    public function toString(): string;

    /**
     * Returns the Secret as a URI.
     */
    public function toUri(): string;
}
