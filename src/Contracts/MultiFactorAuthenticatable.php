<?php

namespace Kohaku1907\LaraMfa\Contracts;

interface MultiFactorAuthenticatable
{
    public function registerMultiFactorAuthentication(): void;

    public static function getAvailableFactors(): array;

    public function hasMultiFactorEnabled(?string $channel): bool;

    public function multiFactorAuthRedirect(): mixed;
}
