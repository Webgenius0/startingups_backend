<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $statusMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct($statusMessage)
    {
        $this->statusMessage = $statusMessage;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Subscription Status Update')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->statusMessage)
            ->line('Thank you for using our service!');
    }
}