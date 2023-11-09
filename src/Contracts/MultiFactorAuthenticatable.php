<?php

namespace Kohaku1907\LaraMfa\Contracts;

use Kohaku1907\LaraMfa\Enums\Channel;

interface MultiFactorAuthenticatable
{
    public function enableSmsMFAuth(string $code): void;

    public function enableEmailMFAuth(string $code): void;

    public function enableTotpMFAuth(string $code): void;

    public function hasMultiFactorEnabled(Channel $channel): bool;
}
