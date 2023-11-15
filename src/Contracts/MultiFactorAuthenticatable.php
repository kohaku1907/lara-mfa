<?php

namespace Kohaku1907\LaraMfa\Contracts;

use Kohaku1907\LaraMfa\Enums\Channel;

interface MultiFactorAuthenticatable
{
    public function registerMultiFactorAuthentication(): void;

    public static function getAvailableFactors(): array;

    public function hasMultiFactorEnabled(?Channel $channel): bool;

    public function multiFactorAuthRedirect(): mixed;
}
