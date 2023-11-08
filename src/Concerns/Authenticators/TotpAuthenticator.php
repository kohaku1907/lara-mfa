<?php

namespace Kohaku1907\LaraMfa\Concerns\Authenticators;
use Kohaku1907\LaraMfa\Enums\Channel;

class TotpAuthenticator extends MFAuthenticator
{
    public function __construct(string $code)
    {
        parent::__construct($code);
        $this->channel = Channel::Totp;
    }
}