<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
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
        'channel' => Channel::class,
        'shared_secret' => 'encrypted',
        'digits' => 'int',
        'seconds' => 'int',
        'window' => 'int',
        'recovery_codes' => 'encrypted:collection',
        'safe_devices' => 'collection',
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
     * Get the authentication strategy for this MFA instance.
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
     * Generate a new code for the current authentication strategy.
     */
    public function generateCode(): string
    {
        $strategy = $this->getAuthenticationStrategy();
        $cacheKey = $this->getCacheKey();
        $cacheDuration = $this->getCacheDuration();

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $code = $strategy->generateCode();
        Cache::put($cacheKey, $code, $cacheDuration);

        return $code;
    }

    /**
     * Send a code using the current authentication strategy.
     */
    public function sendCode(): void
    {
        if ($this->channel === Channel::Totp) {
            return;
        }

        $notificationClass = Config::get("mfa.{$this->channel}.notification");
        $this->authenticatable->notify(new $notificationClass($this->generateCode()));
    }

    /**
     * Verify the given code against the current authentication strategy.
     */
    public function verifyCode(string $code): bool
    {
        if($this->getAuthenticationStrategy()->verifyCode($code)) {
            $expireTime = Config::get("mfa.{$this->channel}.expire_time");
            request()->session()->put("mfa.{$this->channel}.expired_at", now()->addMinutes($expireTime)->getTimestamp());

            Cache::forget($this->getCacheKey());
            return true;
        }
        return false;
    }

    public function getCode(): string
    {
        return Cache::get($this->getCacheKey());
    }

    /**
     * Get the cache key for the current MFA instance.
     */
    protected function getCacheKey(): string
    {
        return "mfa:{$this->id}:code";
    }

    /**
     * Get the cache duration for the current MFA instance.
     */
    protected function getCacheDuration(): int
    {
        $channelConfigKey = "mfa.{$this->channel}.code_timeout";
        $defaultConfigKey = "mfa.default_code_timeout";

        return Config::get($channelConfigKey, Config::get($defaultConfigKey));
    }
}
