<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte supprimé — Hello Passenger</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1a1a1a; padding: 24px; text-align: center; }
        .header img { max-width: 180px; height: auto; }
        .body { padding: 32px 24px; color: #333; }
        .body h1 { font-size: 22px; margin: 0 0 16px; color: #111; }
        .body p { font-size: 15px; line-height: 1.6; margin: 0 0 12px; }
        .info-box { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 4px; margin: 20px 0; }
        .info-box p { margin: 0; font-size: 14px; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 13px; color: #6b7280; }
        .footer a { color: #f59e0b; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('HP-Logo-White.png') }}" alt="Hello Passenger" style="max-width:180px;">
        </div>
        <div class="body">
            <h1>Votre compte a été supprimé</h1>
            <p>Bonjour {{ $client->prenom ?? '' }} {{ $client->nom ?? '' }},</p>
            <p>Nous vous confirmons que votre compte HelloPassenger associé à l'adresse <strong>{{ $client->email }}</strong> a été <strong>supprimé définitivement</strong>.</p>

            <div class="info-box">
                <p><strong>Ce qui a été supprimé :</strong></p>
                <ul style="margin:8px 0 0 0; padding-left:20px; font-size:14px;">
                    <li>Vos informations personnelles (nom, prénom, email, téléphone, adresse)</li>
                    <li>Vos méthodes de paiement enregistrées</li>
                    <li>Vos données de profil</li>
                </ul>
            </div>

            <p>Conformément au Règlement Général sur la Protection des Données (RGPD), toutes vos données personnelles ont été effacées de nos systèmes.</p>
            <p><em>Note : Certaines données liées à des réservations en cours ou à des obligations légales (facturation, comptabilité) peuvent être conservées pendant la durée requise par la loi.</em></p>

            <p>Si vous souhaitez recréer un compte à l'avenir, vous pourrez le faire depuis notre site web.</p>

            <p>Pour toute question, contactez-nous à <a href="mailto:contact@hellopassenger.com">contact@hellopassenger.com</a>.</p>

            <p>Cordialement,<br>L'équipe Hello Passenger</p>
        </div>
        <div class="footer">
            <p>Aéroport de Paris CDG & Orly — Support : <a href="mailto:contact@hellopassenger.com">contact@hellopassenger.com</a></p>
            <p>© {{ date('Y') }} Hello Passenger. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
