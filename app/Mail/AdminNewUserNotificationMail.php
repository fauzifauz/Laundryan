<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewUserNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $method;

    public function __construct($user, $method)
    {
        $this->user = $user;
        $this->method = $method;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New User Registration Notification',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin_new_user',
        );
    }
}
