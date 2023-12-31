<?php

namespace Kohaku1907\LaraMfa\Rules;

use Illuminate\Contracts\Auth\Authenticatable;
use Closure;
use Kohaku1907\LaraMfa\Contracts\MultiFactorAuthenticatable;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @internal
 */
class EmailCode implements ValidationRule
{
    /**
     * Create a new "email code" rule instance.
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
            || !$this->user->createEmailFactorAuth()->verify($value)) {
                $fail(trans('lara-mfa::validation.email_code'));
        }
    }
}