<?php

namespace Kohaku1907\LaraMfa\Concerns;
use Kohaku1907\LaraMfa\Models\MultiFactorAuthSetting as MFASetting;

trait HasMFAuthRecoveryCode
{
    use HasMFASetting;

    /**
     * Determines if the User has Recovery Codes available.
     */
    protected function hasRecoveryCodes(): bool
    {
        return $this->mfaSetting->containsUnusedRecoveryCodes();
    }

    /**
     * Return the current set of Recovery Codes.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRecoveryCodes(): Collection
    {
        return $this->mfaSetting->recovery_codes ?? collect();
    }

    /**
     * Generates a new set of Recovery Codes.
     *
     * @return \Illuminate\Support\Collection
     */
    public function generateRecoveryCodes(): Collection
    {
        [
            'mfa.recovery.codes' => $amount,
            'mfa.recovery.length' => $length
        ] = config()->get([
            'mfa.recovery.codes', 'mfa.recovery.length',
        ]);

        $this->mfaSetting->recovery_codes = MFASetting::generateRecoveryCodes($amount, $length);
        $this->mfaSetting->save();

        return $this->mfaSetting->recovery_codes;
    }

    /**
     * Uses a one-time Recovery Code if there is one available.
     *
     * @return mixed
     */
    protected function useRecoveryCode(string $code): bool
    {
        if (! $this->mfaSetting->setRecoveryCodeAsUsed($code)) {
            return false;
        }

        $this->mfaSetting->save();

        return true;
    }
}
