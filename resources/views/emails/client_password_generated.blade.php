<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Votre mot de passe HelloPassenger</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 90%; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        h1 { color: #1f2937; }
        .credentials { background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .credential-item { margin: 10px 0; }
        .label { font-weight: bold; color: #FFC107; }
        .value { font-family: monospace; font-size: 14px; color: #1f2937; background-color: #fff; padding: 5px; border: 1px solid #ddd; display: inline-block; min-width: 200px; word-break: break-all; }
        .warning { background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 10px; margin: 20px 0; }
        .footer { margin-top: 20px; font-size: 0.9em; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Votre mot de passe HelloPassenger</h1>
        
        <p>Bonjour {{ $client->prenom ?? 'Client' }},</p>
        
        <p>Un mot de passe a été généré pour votre compte HelloPassenger.</p>
        
        <div class="credentials">
            <div class="credential-item">
                <span class="label">Email :</span>
                <span class="value">{{ $client->email }}</span>
            </div>
            <div class="credential-item">
                <span class="label">Mot de passe :</span>
                <span class="value">{{ $password }}</span>
            </div>
            <div class="credential-item" style="margin-top: 10px;">
                <span class="label" style="font-size: 12px; color: #666;">⚠️ Copiez le mot de passe COMPLET sans espaces</span>
            </div>
        </div>
        
        <div class="warning">
            <strong>⚠️ Important :</strong> Veuillez conserver ce mot de passe en sécurité. Vous pouvez le changer après votre première connexion.
        </div>
        
        <p>Vous pouvez maintenant vous connecter à votre compte pour accéder à toutes vos commandes.</p>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement par le système HelloPassenger.</p>
            <p>Si vous n'avez pas demandé ce mot de passe, veuillez ignorer cet email.</p>
        </div>
    </div>
</body>
</html>
