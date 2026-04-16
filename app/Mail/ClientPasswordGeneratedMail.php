<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientPasswordGeneratedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $client;
    public $password;
    public $lang;

    public function __construct(Client $client, string $password, string $lang = 'fr')
    {
        $this->client = $client;
        $this->password = $password;
        $this->lang = $lang;
    }

    public function envelope(): Envelope
    {
        $subject = $this->lang === 'en' 
            ? 'Your HelloPassenger password' 
            : 'Votre mot de passe HelloPassenger';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client_password_generated',
        );
    }
}
