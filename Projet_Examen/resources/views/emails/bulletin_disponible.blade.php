<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nouveau bulletin disponible</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .stats { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Nouveau bulletin disponible</h1>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $parent_nom }}</strong>,</p>
            
            <p>Le bulletin de <strong>{{ $eleve_nom }}</strong> pour la période <strong>{{ $periode }}</strong> ({{ $annee_scolaire }}) est maintenant disponible sur le portail scolaire.</p>
            
            <div class="stats">
                <h3>Résumé des résultats :</h3>
                <ul>
                    <li><strong>Moyenne générale :</strong> {{ $moyenne_generale }}/20</li>
                    <li><strong>Mention :</strong> {{ $mention }}</li>
                    <li><strong>Rang dans la classe :</strong> {{ $rang }}ème</li>
                </ul>
            </div>
            
            <p>Vous pouvez consulter le bulletin complet en vous connectant à votre espace parent sur le portail scolaire.</p>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/login') }}" class="btn">Accéder au portail</a>
            </p>
            
            <p><em>Ce message est envoyé automatiquement. Merci de ne pas y répondre.</em></p>
        </div>
        
        <div class="footer">
            <p>Portail Scolaire - Gestion Administrative</p>
            <p>Pour toute question, contactez l'administration de l'établissement.</p>
        </div>
    </div>
</body>
</html> 