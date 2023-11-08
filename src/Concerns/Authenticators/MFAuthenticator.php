<?php

namespace Kohaku1907\LaraMfa\Concerns\Authenticators;

use Kohaku1907\LaraMfa\Enums\Channel;

abstract class MFAuthenticator
{
    protected Channel $channel;

    protected string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function verify(): bool
    {
        // Verify the code against the user's stored code
        return true;
    }
}
