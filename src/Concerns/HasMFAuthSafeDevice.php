<?php

namespace Kohaku1907\LaraMfa\Concerns;
use Illuminate\Http\Request;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthSetting as MFASetting;

trait HasMFAuthSafeDevice
{
    use HasMFASetting;
     /**
     * Adds a "safe" Device from the Request, and returns the token used.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function addSafeDevice(Request $request): string
    {
        [$name, $expiration] = array_values(config()->get([
            'mfa.safe_devices.cookie', 'mfa.safe_devices.expiration_days',
        ]));

        $this->mfaSetting->safe_devices = $this->safeDevices()
            ->push([
                'mfa_remember' => $token = $this->generateMFRemember(),
                'ip'           => $request->ip(),
                'added_at'     => $this->freshTimestamp()->getTimestamp(),
            ])
            ->sortByDesc('added_at') // Ensure the last is the first, so we can slice it.
            ->slice(0, config('mfa.safe_devices.max_devices', 5))
            ->values();

        $this->mfaSetting->save();

        cookie()->queue($name, $token, $expiration * 1440);

        return $token;
    }

    /**
     * Generates a Device token to bypass Multi-Factor Authentication.
     *
     * @return string
     */
    protected function generateMFRemember(): string
    {
        return MFASetting::generateDefaultRemember();
    }

    /**
     * Deletes all saved safe devices.
     *
     * @return bool
     */
    public function flushSafeDevices(): bool
    {
        return $this->mfaSetting->setAttribute('safe_devices', null)->save();
    }

    /**
     * Return all the Safe Devices that bypass Multi-Factor Authentication.
     *
     * @return \Illuminate\Support\Collection
     */
    public function safeDevices(): Collection
    {
        return $this->mfaSetting->safe_devices ?? collect();
    }

    /**
     * Determines if the Request has been made through a previously used "safe" device.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function isSafeDevice(Request $request): bool
    {
        $timestamp = $this->mfaSetting->getSafeDeviceTimestamp($this->getMultiFactorRememberFromRequest($request));

        if ($timestamp) {
            return $timestamp->addDays(config('mfa.safe_devices.expiration_days'))->isFuture();
        }

        return false;
    }

    /**
     * Returns the Multi-Factor Remember Token of the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getMultiFactorRememberFromRequest(Request $request): ?string
    {
        return $request->cookie(config('mfa.safe_devices.cookie', 'mfa_remember'));
    }

    /**
     * Determines if the Request has been made through a not-previously-known device.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function isNotSafeDevice(Request $request): bool
    {
        return ! $this->isSafeDevice($request);
    }
}