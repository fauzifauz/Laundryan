<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BruteForceAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $lockoutMinutes;

    public function __construct($user, $lockoutMinutes = 1)
    {
        $this->user = $user;
        $this->lockoutMinutes = $lockoutMinutes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Security Alert: Multiple Failed Login Attempts',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.security.brute_force',
        );
    }
}
