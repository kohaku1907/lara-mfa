<?php

namespace Kohaku1907\LaraMfa\Exceptions;

use Exception;

class ResendCodeIntervalNotElapsedException extends Exception
{
    public function __construct()
    {
        parent::__construct(trans("The resend interval has not elapsed yet."));
    }
}