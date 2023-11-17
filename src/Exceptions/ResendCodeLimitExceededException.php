<?php

namespace Kohaku1907\LaraMfa\Exceptions;

use Exception;

class ResendCodeLimitExceededException extends Exception
{
    public function __construct()
    {
        parent::__construct(trans("Resend code limit has been exceeded."));
    }
}