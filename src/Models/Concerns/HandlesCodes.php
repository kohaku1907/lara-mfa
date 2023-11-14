<?php

namespace Kohaku1907\LaraMfa\Models\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Kohaku1907\LaraMfa\Enums\Channel;
use Kohaku1907\LaraMfa\Models\Strategies\AuthenticationStrategy;
use Kohaku1907\LaraMfa\Models\Strategies\EmailAuthentication;
use Kohaku1907\LaraMfa\Models\Strategies\SmsAuthentication;
use Kohaku1907\LaraMfa\Models\Strategies\TotpAuthentication;

trait HandlesCodes
{
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
     *
     * @param  bool  $renew
     */
    public function generateCode($renew = false): string
    {
        if ($this->channel === Channel::Totp) {
            return $this->getAuthenticationStrategy()->generateCode();
        }

        $strategy = $this->getAuthenticationStrategy();
        $cacheKey = $this->getCacheKey();
        $cacheDuration = $this->getCacheDuration();

        if (! $renew && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $code = $strategy->generateCode();
        Cache::put($cacheKey, $code, $cacheDuration);

        return $code;
    }

    /**
     * Send a new code using the current authentication strategy.
     */
    public function sendCode(): bool
    {
        if ($this->channel === Channel::Totp) {
            return false;
        }

        $resendInterval = config("mfa.{$this->channel->value}.resend_interval");
        $resendLimitDuration = config("mfa.{$this->channel->value}.resend_limit_duration");
        $resendLimit = config("mfa.{$this->channel->value}.resend_limit");
        $lastSentKey = "mfa.{$this->channel->value}.last_sent";
        $countSentKey = "mfa.{$this->channel->value}.sent_count";
        $lastSent = Cache::get($lastSentKey);
        $countSent = Cache::get($countSentKey, 0);

        if ($lastSent && now()->diffInSeconds(Carbon::createFromTimestamp($lastSent)) < $resendInterval) {
            return false;
        }

        if ($lastSent && now()->diffInMinutes(Carbon::createFromTimestamp($lastSent)) >= $resendLimitDuration) {
            Cache::forget($lastSentKey);
            Cache::forget("mfa.{$this->channel->value}.sent_count");
        }

        if (! $lastSent || $countSent < $resendLimit) {
            Cache::put($lastSentKey, now()->getTimestamp(), $resendLimitDuration * 60);
            $sentCount = Cache::get($countSentKey, 0);
            $sentCount++;
            Cache::put($countSentKey, $sentCount, $resendLimitDuration * 60);
            $this->send();

            return true;
        }

        return false;
    }

    protected function send(): void
    {
        $notificationClass = Config::get("mfa.{$this->channel->value}.notification");
        $code = $this->generateCode(true);
        $this->authenticatable->notify(new $notificationClass($code));
    }

    /**
     * Verify the given code against the current authentication strategy.
     */
    public function verifyCode(string $code): bool
    {
        if ($this->getAuthenticationStrategy()->verifyCode($code)) {
            $expireTime = Config::get("mfa.{$this->channel->value}.expire_time");
            request()->session()->put("mfa.{$this->channel->value}.expired_at", now()->addMinutes($expireTime)->getTimestamp());

            Cache::forget($this->getCacheKey());

            return true;
        }

        return false;
    }

    public function getCode(): string
    {
        return Cache::get($this->getCacheKey());
    }

    public function enable(string $code): bool
    {
        return $this->getAuthenticationStrategy()->enable($code);
    }

    public function disable(string $code): bool
    {
        return $this->getAuthenticationStrategy()->disable($code);
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
        $channelConfigKey = "mfa.{$this->channel->value}.code_timeout";
        $defaultConfigKey = 'mfa.default_code_timeout';

        return Config::get($channelConfigKey, Config::get($defaultConfigKey));
    }
}
