<?php

namespace Kohaku1907\LaraMfa\Rules;

use Illuminate\Contracts\Auth\Authenticatable;
use Closure;
use Kohaku1907\LaraMfa\Contracts\MultiFactorAuthenticatable;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @internal
 */
class SmsCode implements ValidationRule
{
    /**
     * Create a new "sms code" rule instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user
     */
    public function __construct(protected ?Authenticatable $user = null)
    {
        //
    }

    /**
     * Validate that an attribute is a valid Multi-Factor Authentication TOTP code.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure $fail
     * @return bool
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!is_string($value)
            || !$this->user instanceof MultiFactorAuthenticatable
            || !$this->user->createSmsFactorAuth()->verify($value)) {
                $fail(trans('lara-mfa::validation.sms_code'));
        }
    }

    
}