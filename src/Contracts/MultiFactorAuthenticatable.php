<?php

namespace Kohaku1907\LaraMfa\Contracts;

use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthentication;

interface MultiFactorAuthenticatable
{
    public function createSmsMFAuth(): MultiFactorAuthentication;
    public function createEmailMFAuth(): MultiFactorAuthentication;
    public function createTotpMFAuth(): MultiFactorAuthentication;

    public function enableSmsMFAuth(string $code): void;
    public function enableEmailMFAuth(string $code): void;
    public function enableTotpMFAuth(string $code): void;

    public function disableSmsMFAuth(string $code): void;
    public function disableEmailMFAuth(string $code): void;
    public function disableTotpMFAuth(string $code): void;

    public function hasMultiFactorEnabled(Channel $channel): bool;

    public function getMfaRedirectRoute(): ?string;

    public function setMfaRedirectRoute(): ?string;
}
