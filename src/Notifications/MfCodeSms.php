<?php

namespace Kohaku1907\LaraMfa\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class MfCodeSms extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $code,
    ) {
    }

    public function via(mixed $notifiable): array
    {
        return [TwilioChannel::class];
    }

    public function toTwilio($notifiable): TwilioSmsMessage
    {
        return (new TwilioSmsMessage())
            ->content('Your code is: '.$this->code);
    }
}
