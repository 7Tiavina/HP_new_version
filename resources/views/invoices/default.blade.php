@php
    $baseRef = $commande->id_api_commande ?? $commande->paymentClient->monetico_order_id ?? $commande->id;
    
    // Format invoice/commande number with airport prefix
    $orlyAirportId = '64f00ace-31b6-45b0-bcb2-b562b1ac08d9';
    $cdgAirportId = '88bb89e0-b966-4420-9ed3-7a6745e4d947';
    $airportId = $commande->id_plateforme ?? null;
    
    if ($airportId === $orlyAirportId) {
        $invoiceRef = 'F-ORY-' . $baseRef;
    } elseif ($airportId === $cdgAirportId) {
        $invoiceRef = 'F-CDG-' . $baseRef;
    } else {
        $invoiceRef = $baseRef;
    }
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture #{{ $invoiceRef }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .invoice-container {
            width: 100%;
            margin: 0 auto;
        }
        .header-table, .info-table, .items-table, .total-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            padding: 10px 0;
            vertical-align: top;
        }
        .info-table {
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .info-table td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .company-details p, .client-details p {
            margin: 0;
            line-height: 1.4;
        }
        .company-details h2 {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: bold;
        }
        .items-table {
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .items-table .text-center { text-align: center; }
        .items-table .text-right { text-align: right; }
        .total-section {
            width: 40%;
            margin-left: 60%;
        }
        .total-table td {
            padding: 5px;
        }
        .total-table .label {
            font-weight: bold;
        }
        .total-table .value {
            text-align: right;
        }
        .total-table .grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #333;
            padding-top: 8px;
        }
        .footer-section {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
        .footer-section p {
            margin: 2px 0;
        }
        h1 {
            font-size: 24px;
            margin: 0;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- En-tête -->
        <table class="header-table">
            <tr>
                <td>
                    <!-- Logo temporairement retiré pour diagnostic -->
                </td>
                <td style="text-align: right;">
                    <h1>FACTURE</h1>
                    <p><strong>Facture n°:</strong> {{ $invoiceRef }}</p>
                    <p><strong>Date:</strong> {{ $commande->created_at->format('d/m/Y') }}</p>
                </td>
            </tr>
        </table>

        <!-- Informations Société et Client -->
        <table class="info-table">
            <tr>
                <td class="client-details">
                    <p style="font-weight: bold; margin-bottom: 10px;">Facturé à :</p>
                    <p><strong>{{ $commande->client_prenom }} {{ $commande->client_nom }}</strong></p>
                    @if($commande->client_adresse)
                        <p>{{ $commande->client_adresse }}</p>
                        <p>{{ $commande->client_code_postal }} {{ $commande->client_ville }}</p>
                    @endif
                    <p>{{ $commande->client_email }}</p>
                </td>
                <td class="company-details" style="text-align: right;">
                    <h2>Bagages du Monde</h2>
                    <p>9 RUE DU NOYER ZA DU MOULIN</p>
                    <p>95700 ROISSY-EN-FRANCE</p>
                    <p><strong>Tél:</strong> +33 (0)1 34 38 58 98</p>
                    <p><strong>E-Mail:</strong> contact@hellopassenger.com</p>
                    <p><strong>Site Web:</strong> hellopassenger.com</p>
                </td>
            </tr>
        </table>
        
        <!-- Lignes de la commande -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-center">Quantité</th>
                    <th class="text-right">Prix Unitaire HT</th>
                    <th class="text-right">Total HT</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $details = json_decode($commande->details_commande_lignes, true);
                    $taux_tva = 1.20;
                @endphp
                @foreach($details as $item)
                    <tr>
                        <td>{{ $item['libelleProduit'] }}</td>
                        <td class="text-center">{{ $item['quantite'] }}</td>
                        <td class="text-right">{{ number_format(($item['prixTTC'] / $item['quantite']) / $taux_tva, 2, ',', ' ') }} €</td>
                        <td class="text-right">{{ number_format($item['prixTTC'] / $taux_tva, 2, ',', ' ') }} €</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total -->
        <div class="total-section">
             @php
                $prix_ttc = $commande->total_prix_ttc;
                $prix_ht = $prix_ttc / $taux_tva;
                $montant_tva = $prix_ttc - $prix_ht;
            @endphp
            <table class="total-table">
                <tr>
                    <td class="label">Total HT</td>
                    <td class="value">{{ number_format($prix_ht, 2, ',', ' ') }} €</td>
                </tr>
                <tr>
                    <td class="label">TVA (20%)</td>
                    <td class="value">{{ number_format($montant_tva, 2, ',', ' ') }} €</td>
                </tr>
                <tr class="grand-total">
                    <td class="label">Total TTC</td>
                    <td class="value">{{ number_format($prix_ttc, 2, ',', ' ') }} €</td>
                </tr>
            </table>
        </div>

        <!-- Pied de page -->
        <div class="footer-section">
            <p>Merci pour votre confiance !</p>
            <p><strong>Bagages du Monde</strong> - 9 RUE DU NOYER ZA DU MOULIN, 95700 ROISSY-EN-FRANCE</p>
            <p><strong>Siret :</strong> 43919478800055 | <strong>TVA :</strong> FR08439194788</p>
        </div>
    </div>
</body>
</html>
