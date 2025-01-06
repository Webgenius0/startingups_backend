<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $guestEmail;

    /**
     * Create a new message instance.
     */
    public function __construct($event, $guestEmail)
    {
        $this->event = $event;
        $this->guestEmail = $guestEmail;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('You Are Invited to ' . $this->event->title)
                    ->view('emails.event_invite');
    }
}
