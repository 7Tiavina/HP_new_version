<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe administrateur réinitialisé</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 90%; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        h1 { color: #1f2937; }
        .credentials { background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .credential-item { margin: 10px 0; }
        .label { font-weight: bold; color: #FFC107; }
        .value { font-family: monospace; font-size: 14px; color: #1f2937; }
        .warning { background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 10px; margin: 20px 0; }
        .footer { margin-top: 20px; font-size: 0.9em; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mot de passe administrateur réinitialisé</h1>
        
        <p>Le mot de passe du compte administrateur a été réinitialisé avec succès.</p>
        
        <div class="credentials">
            <div class="credential-item">
                <span class="label">Email :</span>
                <span class="value">{{ $user->email }}</span>
            </div>
            <div class="credential-item">
                <span class="label">Nouveau mot de passe :</span>
                <span class="value">{{ $newPassword }}</span>
            </div>
        </div>
        
        <div class="warning">
            <strong>⚠️ Important :</strong> Veuillez conserver ce nouveau mot de passe en sécurité et le changer après la première connexion.
        </div>
        
        <p>Le compte peut maintenant être utilisé avec ce nouveau mot de passe pour se connecter à l'interface d'administration.</p>
        
        <div class="footer">
            <p>Cet email a été envoyé automatiquement par le système HelloPassenger.</p>
        </div>
    </div>
</body>
</html>
