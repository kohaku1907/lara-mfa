<?php

namespace Kohaku1907\LaraMfa\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Kohaku1907\LaraMfa\Models\Concerns\HandlesRecoveryCodes;
use Kohaku1907\LaraMfa\Models\Concerns\HandlesSafeDevices;

class MultiFactorSetting extends Model
{
    use HasFactory;
    use HandlesSafeDevices;
    use HandlesRecoveryCodes;

    protected $table = 'multi_factor_settings';

    protected $guarded = [];

    protected $casts = [
        'recovery_codes' => 'encrypted:collection',
        'safe_devices' => 'collection',
    ];

    /**
     * The model that uses this Authentication.
     */
    public function authenticatable(): MorphTo
    {
        return $this->morphTo('authenticatable');
    }
}
