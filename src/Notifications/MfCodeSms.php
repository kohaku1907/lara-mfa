<?php

namespace Kohaku1907\LaraMfa\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\VonageMessage;

class MfCodeSms extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $code,
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['nexmo'];
    }

    public function toNexmo($notifiable): VonageMessage
    {
        return (new VonageMessage)
            ->content('Your code is: ' . $this->code);
    }
}