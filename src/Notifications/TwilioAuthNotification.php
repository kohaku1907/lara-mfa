<?php

namespace Kohaku1907\LaraMfa\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Twilio\TwilioSmsMessage;

class TwilioAuthNotification extends DefaultAuthNotification implements ShouldQueue
{
    use Queueable;

    public function toTwilio($notifiable)
    {
        return (new TwilioSmsMessage())
            ->content($this->getNotificationPlainText());
    }
}