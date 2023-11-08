<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\Strategies\AuthenticationStrategy;
use Kohaku1907\LaraMfa\Models\Strategies\EmailAuthentication;
use Kohaku1907\LaraMfa\Models\Strategies\SmsAuthentication;
use Kohaku1907\LaraMfa\Models\Strategies\TotpAuthentication;

class MultiFactorAuthentication extends Model
{
    
    use HasFactory;

    protected $table = 'multi_factor_authentications';

    protected $guarded = [];

    protected $casts = [
        'channel'                     => Channel::class,
        'shared_secret'               => 'encrypted',
        'digits'                      => 'int',
        'seconds'                     => 'int',
        'window'                      => 'int',
        'recovery_codes'              => 'encrypted:collection',
        'safe_devices'                => 'collection',
        'enabled_at'                  => 'datetime',
    ];


     /**
     * The model that uses 2Step Authentication.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function authenticatable(): MorphTo
    {
        return $this->morphTo('authenticatable');
    }

    /**
     * Get the authentication strategy for this MFA instance.
     *
     * @return \Kohaku1907\LaraMfa\Models\Strategies\AuthenticationStrategy
     */
    public function getAuthenticationStrategy(): AuthenticationStrategy
    {
        switch ($this->channel) {
            case Channel::Totp:
                return new TotpAuthentication($this);
            case Channel::Sms:
                return new SmsAuthentication($this);
            case Channel::Email:
                return new EmailAuthentication($this);
            default:
                throw new \InvalidArgumentException("Invalid MFA channel: {$this->channel}");
        }
    }

    /**
     * Verify the given code against the current authentication strategy.
     *
     * @param string $code
     * @return bool
     */
    public function verifyCode(string $code): bool
    {
        return $this->getAuthenticationStrategy()->verifyCode($code);
    }

    /**
     * Generate a new code for the current authentication strategy.
     *
     * @return string
     */
    public function generateCode(): string
    {
        return $this->getAuthenticationStrategy()->generateCode();
    }

    /**
     * Send a new code using the current authentication strategy.
     *
     * @return void
     */
    public function sendCode(): void
    {
        $this->getAuthenticationStrategy()->sendCode();
    }
}