@php
    $isEn = ($lang ?? 'fr') === 'en';

    // First, try to extract the invoice number directly from the PDF (same as in the PDF invoice from BDM API)
    $orderRef = $commande->getInvoiceNumberFromPdf();

    // Fallback: Format invoice/commande number with airport prefix
    if (!$orderRef) {
        $baseRef = $commande->id_api_commande ?? $commande->paymentClient->monetico_order_id ?? $commande->id;
        $orlyAirportId = '64f00ace-31b6-45b0-bcb2-b562b1ac08d9';
        $cdgAirportId = '88bb89e0-b966-4420-9ed3-7a6745e4d947';
        $airportId = $commande->id_plateforme ?? null;

        if ($airportId === $orlyAirportId) {
            $orderRef = 'F-ORY-' . $baseRef;
        } elseif ($airportId === $cdgAirportId) {
            $orderRef = 'F-CDG-' . $baseRef;
        } else {
            $orderRef = $baseRef;
        }
    }
@endphp
<!DOCTYPE html>
<html lang="{{ $isEn ? 'en' : 'fr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $isEn ? 'Your HelloPassenger order confirmation' : 'Confirmation de votre commande HelloPassenger' }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 90%; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        h1 { color: #1f2937; }
        strong { color: #FFC107; }
        .footer { margin-top: 20px; font-size: 0.9em; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $isEn ? 'Your booking is confirmed!' : 'Votre réservation est confirmée !' }}</h1>
        
        <p>{{ $isEn ? 'Hello' : 'Bonjour' }} {{ $commande->client_prenom }} {{ $commande->client_nom }},</p>

        <p>
            {{ $isEn
                ? 'Thank you for your trust. We are pleased to confirm receipt of your order #' . $orderRef . '.'
                : 'Nous vous remercions chaleureusement pour votre confiance et nous avons le plaisir de vous confirmer la bonne réception de votre commande n° ' . $orderRef . '.'
            }}
        </p>

        <p>{{ $isEn ? 'Your detailed invoice is attached as a PDF.' : 'Vous trouverez en pièce jointe votre facture détaillée au format PDF.' }}</p>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <h2>{{ $isEn ? 'Order details:' : 'Détails de votre commande :' }}</h2>
        <ul>
            <li><strong>{{ $isEn ? 'Invoice #:' : 'Facture n° :' }}</strong> {{ $orderRef }}</li>
            <li><strong>{{ $isEn ? 'Order date:' : 'Date de commande :' }}</strong> {{ $commande->created_at->format('d/m/Y H:i') }}</li>
            <li><strong>{{ $isEn ? 'Total paid:' : 'Total payé :' }}</strong> {{ number_format($commande->total_prix_ttc, 2, ',', ' ') }} €</li>
            <!-- Vous pouvez ajouter d'autres détails ici si vous le souhaitez -->
        </ul>

        <p>{{ $isEn ? 'The HelloPassenger team wishes you a pleasant journey!' : 'Toute l\'équipe de HelloPassenger vous souhaite un excellent voyage !' }}</p>
        
        <div class="footer">
            <p>{{ $isEn ? 'Best regards,' : 'Cordialement,' }}</p>
            <p>{{ $isEn ? 'The HelloPassenger team' : 'L\'équipe HelloPassenger' }}</p>
        </div>
    </div>
</body>
</html>
