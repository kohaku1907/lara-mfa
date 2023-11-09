<?php

namespace Kohaku1907\LaraMfa\Contracts;

interface TotpFactor
{
    /**
     * Returns the Secret as a QR Code.
     *
     * @return string
     */
    public function toQr(): string;

    /**
     * Returns the Secret as a string.
     *
     * @return string
     */
    public function toString(): string;

    /**
     * Returns the Secret as a URI.
     *
     * @return string
     */
    public function toUri(): string;
}