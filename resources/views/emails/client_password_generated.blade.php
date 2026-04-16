<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $lang === 'en' ? 'Your HelloPassenger password' : 'Votre mot de passe HelloPassenger' }}</title>
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
        <h1>{{ $lang === 'en' ? 'Your HelloPassenger password' : 'Votre mot de passe HelloPassenger' }}</h1>
        
        <p>{{ $lang === 'en' ? 'Hello' : 'Bonjour' }} {{ $client->prenom ?? 'Client' }},</p>
        
        <p>
            {{ $lang === 'en' 
                ? 'A password has been generated for your HelloPassenger account.' 
                : 'Un mot de passe a été généré pour votre compte HelloPassenger.' }}
        </p>
        
        <div class="credentials">
            <div class="credential-item">
                <span class="label">Email :</span>
                <span class="value">{{ $client->email }}</span>
            </div>
            <div class="credential-item">
                <span class="label">{{ $lang === 'en' ? 'Password :' : 'Mot de passe :' }}</span>
                <span class="value">{{ $password }}</span>
            </div>
            <div class="credential-item" style="margin-top: 10px;">
                <span class="label" style="font-size: 12px; color: #666;">
                    {{ $lang === 'en' 
                        ? '⚠️ Copy the COMPLETE password without spaces' 
                        : '⚠️ Copiez le mot de passe COMPLET sans espaces' }}
                </span>
            </div>
        </div>
        
        <div class="warning">
            <strong>⚠️ {{ $lang === 'en' ? 'Important :' : 'Important :' }}</strong> 
            {{ $lang === 'en' 
                ? 'Please keep this password secure. You can change it after your first login.' 
                : 'Veuillez conserver ce mot de passe en sécurité. Vous pouvez le changer après votre première connexion.' }}
        </div>
        
        <p>
            {{ $lang === 'en' 
                ? 'You can now log in to your account to access all your orders.' 
                : 'Vous pouvez maintenant vous connecter à votre compte pour accéder à toutes vos commandes.' }}
        </p>

        <div class="footer">
            <p>
                {{ $lang === 'en' 
                    ? 'This email was automatically sent by the HelloPassenger system.' 
                    : 'Cet email a été envoyé automatiquement par le système HelloPassenger.' }}
            </p>
            <p>
                {{ $lang === 'en' 
                    ? 'If you did not request this password, please ignore this email.' 
                    : 'Si vous n\'avez pas demandé ce mot de passe, veuillez ignorer cet email.' }}
            </p>
        </div>
    </div>
</body>
</html>
