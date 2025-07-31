<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulletin de notes</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .school-name { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .document-title { font-size: 18px; margin-bottom: 5px; }
        .student-info { margin-bottom: 30px; }
        .info-row { margin-bottom: 10px; }
        .info-label { font-weight: bold; display: inline-block; width: 120px; }
        .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .grades-table th, .grades-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        .grades-table th { background-color: #f5f5f5; font-weight: bold; }
        .summary { margin-bottom: 30px; }
        .summary-row { margin-bottom: 8px; }
        .summary-label { font-weight: bold; display: inline-block; width: 150px; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #666; }
        .signature-section { margin-top: 40px; }
        .signature-line { border-top: 1px solid #333; width: 200px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">ÉTABLISSEMENT SCOLAIRE</div>
        <div class="document-title">BULLETIN DE NOTES</div>
        <div>Année scolaire : {{ $bulletin->annee_scolaire }}</div>
        <div>Période : {{ $periodeFormatee }}</div>
    </div>

    <div class="student-info">
        <div class="info-row">
            <span class="info-label">Élève :</span>
            <span>{{ $eleve->user->nom }} {{ $eleve->user->prenom }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Classe :</span>
            <span>{{ $classe->nom }} ({{ $classe->niveau }})</span>
        </div>
        <div class="info-row">
            <span class="info-label">Matricule :</span>
            <span>{{ $eleve->numero_matricule }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date de naissance :</span>
            <span>{{ \Carbon\Carbon::parse($eleve->date_naissance)->format('d/m/Y') }}</span>
        </div>
    </div>

    <table class="grades-table">
        <thead>
            <tr>
                <th>Matière</th>
                <th>Coefficient</th>
                <th>Moyenne</th>
                <th>Appréciation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($moyennesParMatiere as $matiereId => $data)
            <tr>
                <td>{{ $data['matiere']->nom }}</td>
                <td>{{ $data['coefficient'] }}</td>
                <td>{{ $data['moyenne'] }}/20</td>
                <td>{{ $data['moyenne'] >= 15 ? 'Excellent' : ($data['moyenne'] >= 12 ? 'Bon' : ($data['moyenne'] >= 10 ? 'Moyen' : 'Insuffisant')) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Moyenne générale :</span>
            <span>{{ $bulletin->moyenne_generale }}/20</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Rang dans la classe :</span>
            <span>{{ $bulletin->rang }}ème</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Mention :</span>
            <span>{{ $bulletin->mention }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Appréciation générale :</span>
            <span>{{ $bulletin->appreciation }}</span>
        </div>
    </div>

    <div class="signature-section">
        <div style="float: left; width: 45%;">
            <div>Signature du professeur principal :</div>
            <div class="signature-line"></div>
        </div>
        <div style="float: right; width: 45%;">
            <div>Signature du chef d'établissement :</div>
            <div class="signature-line"></div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="footer">
        <p>Document généré automatiquement le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</p>
        <p>Portail Scolaire - Gestion Administrative</p>
    </div>
</body>
</html> 