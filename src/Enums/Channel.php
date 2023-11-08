<?php

namespace Kohaku1907\LaraMfa\Enums;

enum Channel: string
{
    case Email = 'email';
    case Sms = 'sms';
    case Totp = 'totp';
}