<?php

namespace Kohaku1907\LaraMfa\Contracts;

use Kohaku1907\LaraMfa\Enums\Channel;
use Illuminate\Support\Collection;

interface MultiFactorAuthenticatable
{
    public function registerMultiFactorAuthentication(): void;

    public static function getAvailableFactors(): array;

    public function hasMultiFactorEnabled(?Channel $channel): Collection|bool;
    
    public function hasMultiFactorVerified(?Channel $channel = null): Collection|bool;

    public function multiFactorAuthRedirect(): mixed;
}
