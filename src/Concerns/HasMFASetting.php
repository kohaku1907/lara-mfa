<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthSetting as MFASetting;

trait HasMFASetting
{
    public function mfaSetting(): MorphOne
    {
        return $this->morphOne(MFASetting::class, 'authenticatable');
    }
}
