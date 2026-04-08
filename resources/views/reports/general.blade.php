<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; width: 100%; }
        .header { background: #4338ca; color: white; padding: 15px 25px; margin-bottom: 15px; }
        .header h1 { font-size: 18px; font-weight: bold; }
        .header p { font-size: 10px; opacity: 0.85; margin-top: 2px; }
        .content { padding: 0 25px 25px; }
        .section-title { font-size: 12px; font-weight: bold; color: #4338ca; border-bottom: 2px solid #4338ca; padding-bottom: 3px; margin: 20px 0 10px; text-transform: uppercase; }
        
        /* KPI Grid */
        .kpi-grid { display: table; width: 100%; border-spacing: 10px; margin: 0 -10px 10px; }
        .kpi-box { display: table-cell; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center; border-radius: 4px; }
        .kpi-box .val { font-size: 16px; font-weight: bold; color: #1e293b; }
        .kpi-box .lbl { font-size: 8px; color: #64748b; text-transform: uppercase; margin-top: 2px; }
        
        /* Table Style */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9px; }
        th { background: #f1f5f9; color: #475569; padding: 6px 8px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        /* Colors */
        .success { color: #059669; }
        .danger { color: #dc2626; }
        .warning { color: #d97706; }
        
        /* Utils */
        .page-break { page-break-after: always; }
        .footer { text-align: center; font-size: 8px; color: #94a3b8; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 8px; }
        .bg-gray { background: #f1f5f9; }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ $title }}</h1>
    <p>{{ $period_label }} | Rapport Généré le {{ $date }}</p>
</div>

<div class="content">

    <!-- 1. RÉSUMÉ EXÉCUTIF -->
    <div class="section-title">Résumé Exécutif de la Situation Financière</div>
    <div class="kpi-grid">
        <div class="kpi-box"><div class="val success">$ {{ number_format($balance, 2) }}</div><div class="lbl">Solde Global</div></div>
        <div class="kpi-box"><div class="val">$ {{ number_format($available, 2) }}</div><div class="lbl">Disponible</div></div>
        <div class="kpi-box"><div class="val warning">$ {{ number_format($solidarity, 2) }}</div><div class="lbl">Solidarité</div></div>
        <div class="kpi-box"><div class="val danger">$ {{ number_format($total_loaned, 2) }}</div><div class="lbl">Encours Prêts</div></div>
    </div>
    <div class="kpi-grid">
        <div class="kpi-box"><div class="val">{{ $total_members }}</div><div class="lbl">Membres</div></div>
        <div class="kpi-box"><div class="val success">$ {{ number_format($total_contributions, 2) }}</div><div class="lbl">Total Cotisé</div></div>
        <div class="kpi-box"><div class="val danger">{{ $late_contributions }}</div><div class="lbl">Retards</div></div>
    </div>

    <!-- 2. AUDIT DE CAISSE -->
    <div class="section-title">Analyse Détaillée de la Liquidité (Audit)</div>
    <table style="width: 60%;">
        <tr><td>Solde Brut en Caisse</td><td class="text-right font-bold">$ {{ number_format($audit['gross_balance'], 2) }}</td></tr>
        <tr><td>Moins : Prêts Actifs (Encours)</td><td class="text-right danger">- $ {{ number_format($audit['loans_encumbrance'], 2) }}</td></tr>
        <tr class="bg-gray"><td class="font-bold">SOLDE DISPONIBLE RÉEL</td><td class="text-right font-bold success">$ {{ number_format($audit['available_raw'], 2) }}</td></tr>
        <tr><td>Dont part réservée Solidarité</td><td class="text-right warning">$ {{ number_format($audit['solidarity'], 2) }}</td></tr>
    </table>

    <div class="page-break"></div>

    <!-- 3. MEMBRES ET PERFORMANCE -->
    <div class="section-title">Liste des Membres et État des Cotisations</div>
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Nom Complet</th>
                <th>Adhésion</th>
                <th>Performance Score</th>
                <th>Cotis. (Nbr)</th>
                <th>Total Cotisé</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($members as $m)
            <tr>
                <td>{{ $m->member_number }}</td>
                <td class="font-bold">{{ $m->full_name }}</td>
                <td>{{ $m->joined_at->format('d/m/Y') }}</td>
                <td>{{ $m->confidence_score }}%</td>
                <td>{{ $m->contributions_count }}</td>
                <td class="text-right">$ {{ number_format($m->contributions_sum_amount ?? 0, 2) }}</td>
                <td><span class="{{ $m->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($m->status) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- 4. JOURNAL DES COTISATIONS -->
    <div class="section-title">Journal des Cotisations reçues (Période)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Membre</th>
                <th>N° Membre</th>
                <th>N° Reçu</th>
                <th class="text-right">Montant ($)</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contributions as $c)
            <tr>
                <td>{{ \Carbon\Carbon::parse($c->payment_date)->format('d/m/Y') }}</td>
                <td>{{ $c->member->full_name }}</td>
                <td>{{ $c->member->member_number }}</td>
                <td>{{ $c->receipt_number ?? '—' }}</td>
                <td class="text-right font-bold">$ {{ number_format($c->amount, 2) }}</td>
                <td><span class="{{ $c->status === 'paid' ? 'success' : 'danger' }}">{{ $c->status }}</span></td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center">Aucune cotisation sur cette période.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- 5. ÉTAT DES PRÊTS -->
    <div class="section-title">État des Prêts et Remboursements</div>
    <div style="margin-bottom: 5px;"><strong>Prêts ouverts / en cours :</strong></div>
    <table>
        <thead>
            <tr>
                <th>Membre</th>
                <th>Capital ($)</th>
                <th>Durée</th>
                <th>Total dû</th>
                <th>Restant</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loans as $loan)
            <tr>
                <td>{{ $loan->member->full_name }}</td>
                <td>$ {{ number_format($loan->principal_amount, 2) }}</td>
                <td>{{ $loan->term_months }} mois</td>
                <td class="font-bold">$ {{ number_format($loan->total_to_repay, 2) }}</td>
                <td class="text-right danger">$ {{ number_format($loan->balance_remaining, 2) }}</td>
                <td>{{ $loan->status }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center">Aucun prêt sur cette période.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin: 15px 0 5px;"><strong>Historique des remboursements reçus :</strong></div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Membre</th>
                <th>Prêt #</th>
                <th class="text-right">Montant ($)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($repayments as $r)
            <tr>
                <td>{{ \Carbon\Carbon::parse($r->payment_date)->format('d/m/Y') }}</td>
                <td>{{ $r->loan->member->full_name }}</td>
                <td>#{{ $r->loan_id }}</td>
                <td class="text-right font-bold success">$ {{ number_format($r->amount_paid, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- 6. AIDES & DÉPENSES -->
    <div class="section-title">Aides Sociales et Dépenses de Fonctionnement</div>
    <div style="margin-bottom: 5px;"><strong>Demandes d'Aide Sociale :</strong></div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Membre</th>
                <th>Motif</th>
                <th class="text-right">Montant ($)</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($helpRequests as $hr)
            <tr>
                <td>{{ $hr->created_at->format('d/m/Y') }}</td>
                <td>{{ $hr->member->full_name }}</td>
                <td>{{ $hr->reason }}</td>
                <td class="text-right font-bold">$ {{ number_format($hr->amount, 2) }}</td>
                <td>{{ $hr->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin: 15px 0 5px;"><strong>Journal des Dépenses de Fonctionnement :</strong></div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th class="text-right">Montant ($)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $e)
            <tr>
                <td>{{ $e->created_at->format('d/m/Y') }}</td>
                <td>{{ $e->description }}</td>
                <td class="text-right font-bold danger">$ {{ number_format($e->amount, 2) }}</td>
            </tr>
            @endforeach
            <tr class="bg-gray">
                <td colspan="2" class="font-bold">TOTAL DES DÉPENSES</td>
                <td class="text-right font-bold danger">$ {{ number_format($total_expenses, 2) }}</td>
            </tr>
        </tbody>
    </table>

</div>

<div class="footer">
    Ceci est un document officiel de la Mutuelle généré le {{ $date }}. 
    Page 1/1 (Audit Consolidé)
</div>

</body>
</html>

