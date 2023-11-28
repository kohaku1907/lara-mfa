<?php

namespace Kohaku1907\LaraMfa\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DefaultAuthNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The authentication token.
     *
     * @var string
     */
    protected $token;

    /**
     * The channels to dispatch this notification via.
     *
     * @var array
     */
    protected $channels;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token, array $channels)
    {
        $this->token = $token;

        $this->channels = $channels;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return $this->channels;
    }

    /**
     * Get the notification's content.
     *
     * @return string
     */
    protected function getNotificationPlainText()
    {
        return "Your code is: {$this->token}";
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your code')
            ->line($this->getNotificationPlainText())
            ->line('If you did not request this code, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->getNotificationPlainText(),
        ];
    }

}