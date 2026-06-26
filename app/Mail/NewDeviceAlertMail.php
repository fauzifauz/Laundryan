<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewDeviceAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $userAgent;
    public $ip;

    public function __construct($user, $userAgent, $ip)
    {
        $this->user = $user;
        $this->userAgent = $userAgent;
        $this->ip = $ip;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Security Alert: New Device Login Detected',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.security.new_device',
        );
    }
}
