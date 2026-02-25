<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment; // Import pour les pièces jointes
use App\Models\Commande; // Import du modèle Commande

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $commande;
    public ?string $invoiceBase64; // Renommé et rendu nullable
    public ?string $language; // Language for email translation

    /**
     * Create a new message instance.
     */
    public function __construct(Commande $commande, ?string $invoiceBase64 = null, ?string $language = null) // Accepte Base64, nullable
    {
        $this->commande = $commande;
        $this->invoiceBase64 = $invoiceBase64;
        $this->language = $language ?? \Illuminate\Support\Facades\Session::get('app_language', 'fr');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $reference = $this->getFormattedReference();
        $isEn = $this->language === 'en';
        $subject = $isEn 
            ? 'Your HelloPassenger order confirmation #' . $reference
            : 'Confirmation de votre commande n° ' . $reference . ' - HelloPassenger';
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_confirmation',
            with: [
                'commande' => $this->commande,
                'lang' => $this->language,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (!$this->invoiceBase64) {
            \Illuminate\Support\Facades\Log::debug('No invoiceBase64 found to attach for command ' . ($this->commande->id ?? 'unknown'));
            return []; // Pas de facture à attacher
        }

        \Illuminate\Support\Facades\Log::debug('Attempting to attach invoice (first 100 chars): ' . substr($this->invoiceBase64, 0, 100));
        \Illuminate\Support\Facades\Log::debug('Invoice Base64 length to attach: ' . strlen($this->invoiceBase64));

        $reference = $this->getFormattedReference();
        $isEn = $this->language === 'en';
        $filename = $isEn 
            ? 'invoice-' . $reference . '.pdf'
            : 'facture-' . $reference . '.pdf';
        
        return [
            Attachment::fromData(fn () => base64_decode($this->invoiceBase64))
                      ->as($filename)
                      ->withMime('application/pdf'),
        ];
    }

    /**
     * Get formatted reference with airport prefix (F-ORY- or F-CDG-)
     * First tries to extract from PDF, then falls back to formatted id_api_commande
     */
    private function getFormattedReference(): string
    {
        // First, try to extract the invoice number directly from the PDF
        $invoiceNumberFromPdf = $this->commande->getInvoiceNumberFromPdf();
        if ($invoiceNumberFromPdf) {
            return $invoiceNumberFromPdf;
        }

        // Fallback: Use id_api_commande as primary reference and format it
        $baseRef = $this->commande->id_api_commande ?? $this->commande->paymentClient->monetico_order_id ?? $this->commande->id;
        
        $orlyAirportId = '64f00ace-31b6-45b0-bcb2-b562b1ac08d9';
        $cdgAirportId = '88bb89e0-b966-4420-9ed3-7a6745e4d947';
        $airportId = $this->commande->id_plateforme ?? null;
        
        if ($airportId === $orlyAirportId) {
            return 'F-ORY-' . $baseRef;
        } elseif ($airportId === $cdgAirportId) {
            return 'F-CDG-' . $baseRef;
        }
        
        return $baseRef;
    }
}
