<?php

namespace Kohaku1907\LaraMfa\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Kohaku1907\LaraMfa\Contracts\TotpFactor;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\Concerns\HandlesCodes;
use Kohaku1907\LaraMfa\Models\Concerns\SerializesSecret;
use ParagonIE\ConstantTime\Base32;

class MultiFactorAuthentication extends Model implements TotpFactor
{
    use HandlesCodes;
    use HasFactory;
    use SerializesSecret;

    protected $table = 'multi_factor_authentications';

    protected $guarded = [];

    protected $casts = [
        'channel' => Channel::class,
        'secret' => 'encrypted',
        'enabled_at' => 'datetime',
    ];

    /**
     * The model that uses this Authentication.
     */
    public function authenticatable(): MorphTo
    {
        return $this->morphTo('authenticatable');
    }

    /**
     * Creates a new Random Secret.
     */
    public static function generateRandomSecret(): string
    {
        return Base32::encodeUpper(
            random_bytes(config('mfa.totp.secret_length'))
        );
    }
}
