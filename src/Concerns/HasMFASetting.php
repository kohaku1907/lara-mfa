<?php

namespace Kohaku1907\LaraMfa\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthSetting as MFASetting;

trait HasMFASetting
{
    protected function mfaSetting(): MorphOne
    {
        return $this->morphOne(MFASetting::class, 'authenticatable');
    }
}
