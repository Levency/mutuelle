<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; }
        .header { background: #4f46e5; color: white; padding: 15px 25px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; }
        .content { padding: 0 25px 30px; }
        .audit-box { border: 2px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px; padding: 20px; background: #f9fafb; }
        .line { display: block; clear: both; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .line .lbl { float: left; color: #4b5563; }
        .line .val { float: right; font-weight: bold; }
        .line.total { border-top: 2px solid #4f46e5; border-bottom: none; font-size: 14px; color: #4f46e5; margin-top: 10px; }
        .margin-info { font-size: 10px; color: #6b7280; font-style: italic; margin-top: 5px; }
        .section-title { font-size: 13px; font-weight: bold; color: #4f46e5; margin: 20px 0 10px; text-transform: uppercase; }
        .footer { text-align: center; font-size: 9px; color: #9ca3af; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ $title }}</h1>
    <p>État de la liquidité au {{ $date }}</p>
</div>
<div class="content">
    
    <div class="section-title">Analyse du Solde Réel</div>
    <div class="audit-box">
        <div class="line">
            <span class="lbl">Solde Brut en Caisse (Entrées - Sorties)</span>
            <span class="val">$ {{ number_format($gross_balance, 2) }}</span>
        </div>
        <div class="line">
            <span class="lbl">Moins : Prêts Actifs (Encours non remboursés)</span>
            <span class="val" style="color:#dc2626">- $ {{ number_format($loans_encumbrance, 2) }}</span>
        </div>
        <div class="line total">
            <span class="lbl">SOLDE DISPONIBLE BRUT</span>
            <span class="val">$ {{ number_format($available_raw, 2) }}</span>
        </div>
    </div>

    <div class="section-title">Sécurité & Réserves</div>
    <div class="audit-box">
        <div class="line">
            <span class="lbl">Part réservée au Fonds de Solidarité</span>
            <span class="val">$ {{ number_format($solidarity, 2) }}</span>
        </div>

        <div class="line">
            <span class="lbl">Marge de sécurité pour nouveaux Prêts</span>
            <span class="val" style="color:#d97706">{{ $loan_margin }}%</span>
        </div>
        <div class="line">
            <span class="lbl">Marge de sécurité pour nouvelles Aides</span>
            <span class="val" style="color:#d97706">{{ $help_margin }}%</span>
        </div>
        <div class="margin-info">
            Note : Les marges de sécurité sont conservées pour garantir la liquidité face aux imprévus et aux demandes d'aides sociales prioritaires.
        </div>
    </div>

    <p style="font-size: 10px; color: #4b5563; line-height: 1.5;">
        Ce rapport fait office de certification de la liquidité de la mutuelle. Il est utilisé par le conseil d'administration pour valider ou rejeter les nouvelles demandes de décaissement importantes.
    </p>

</div>
<div class="footer">Mutulle — Audit de Liquidité — {{ $date }}</div>
</body>
</html>
