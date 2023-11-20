<?php

namespace Kohaku1907\LaraMfa\Contracts;

use Illuminate\Support\Collection;
use Kohaku1907\LaraMfa\Enums\Channel;

interface MultiFactorAuthenticatable
{
    public function registerMultiFactorAuthentication(): void;

    public static function getAvailableFactors(): array;

    public function hasMultiFactorEnabled(?Channel $channel): Collection|bool;

    public function hasMultiFactorVerified(Channel $channel = null): Collection|bool;

    public function multiFactorAuthRedirect(string $middleware): mixed;
}
