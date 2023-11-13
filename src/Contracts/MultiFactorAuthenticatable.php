<?php

namespace Kohaku1907\LaraMfa\Contracts;

use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication;

interface MultiFactorAuthenticatable
{
    public function registerMultiFactorAuthentication(): void;

    public function getAvailableFactors(): array;

    public function hasMultiFactorEnabled(?string $channel): bool;

    public function multiFactorAuthRedirect(): mixed;
}
