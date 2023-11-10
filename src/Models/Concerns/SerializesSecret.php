<?php

namespace Kohaku1907\LaraMfa\Models\Concerns;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

use function array_values;
use function chunk_split;
use function config;
use function http_build_query;
use function rawurlencode;
use function trim;

trait SerializesSecret
{
    /**
     * Returns the Secret as a URI.
     */
    public function toUri(): string
    {
        $issuer = config('mfa.totp.issuer') ?: config('app.name');
        $label = config('mfa.totp.label') ?: $issuer;
        $query = http_build_query([
            'issuer' => $issuer,
            'label' => $label,
            'secret' => $this->secret,
            'algorithm' => 'sha1',
            'digits' => 6,
        ], '', '&', PHP_QUERY_RFC3986);

        return 'otpauth://totp/'.rawurlencode($issuer).'%3A'.$this->attributes['label']."?$query";
    }

    /**
     * Returns the Shared Secret as a QR Code in SVG format.
     */
    public function toQr(): string
    {
        [$size, $margin] = array_values(config('mfa.qr_code'));

        return (
            new Writer(new ImageRenderer(new RendererStyle($size, $margin), new SvgImageBackEnd()))
        )->writeString($this->toUri());
    }

    /**
     * Returns the current object instance as a string representation.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Returns the Secret as a string.
     */
    public function toString(): string
    {
        return $this->secret;
    }

    /**
     * Returns the Secret as a string of 4-character groups.
     */
    public function toGroupedString(): string
    {
        return trim(chunk_split($this->toString(), 4, ' '));
    }

    /**
     * {@inheritDoc}
     */
    public function render(): string
    {
        return $this->toQr();
    }
}
