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

    public function __construct(Client $client, string $password)
    {
        $this->client = $client;
        $this->password = $password;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre mot de passe HelloPassenger',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client_password_generated',
        );
    }
}
