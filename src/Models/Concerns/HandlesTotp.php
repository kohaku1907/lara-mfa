<?php

namespace Kohaku1907\LaraMfa\Models\Concerns;

use Kohaku1907\LaraMfa\Models\Concerns\TOTP\HandlesCodes;
use Kohaku1907\LaraMfa\Models\Concerns\TOTP\HandlesRecoveryCodes;
use Kohaku1907\LaraMfa\Models\Concerns\TOTP\HandlesSafeDevices;
use Kohaku1907\LaraMfa\Models\Concerns\TOTP\SerializesSharedSecret;

trait HandlesTotp
{
    use HandlesCodes;
    use HandlesRecoveryCodes;
    use HandlesSafeDevices;
    use SerializesSharedSecret;
}
