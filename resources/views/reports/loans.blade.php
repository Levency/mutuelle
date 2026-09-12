<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; }
        .header { background: #4338ca; color: white; padding: 15px 25px; margin-bottom: 15px; }
        .header h1 { font-size: 18px; }
        .header p { font-size: 10px; opacity: 0.85; }
        .content { padding: 0 25px 25px; }
        .summary { background: #eef2ff; border: 1px solid #a5b4fc; padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; }
        .summary span { font-weight: bold; color: #4338ca; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th { background: #4338ca; color: white; padding: 7px 8px; text-align: left; font-size: 10px; }
        tbody tr:nth-child(even) { background: #eef2ff; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #c7d2fe; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-success { background: #d1fae5; color: #059669; }
        .badge-danger  { background: #fee2e2; color: #dc2626; }
        .badge-warning { background: #fef3c7; color: #d97706; }
        .badge-gray    { background: #f3f4f6; color: #6b7280; }
        .footer { text-align: center; font-size: 9px; color: #9ca3af; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ $title }}</h1>
    <p>{{ $period_label }} | Généré le {{ $date }}</p>
</div>
<div class="content">
    <div class="summary">
        Capital total : <span>{{ \App\Models\Setting::get('currency', 'USD') }} {{ number_format($total_principal, 2) }}</span> &nbsp;|&nbsp;
        Encours actifs : <span style="color:#dc2626">{{ \App\Models\Setting::get('currency', 'USD') }} {{ number_format($total_remaining, 2) }}</span> &nbsp;|&nbsp;
        En attente : <span style="color:#d97706">{{ $pending_count }}</span> &nbsp;|&nbsp;
        Actifs : <span style="color:#059669">{{ $active_count }}</span> &nbsp;|&nbsp;
        Remboursés : <span style="color:#6b7280">{{ $repaid_count }}</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Membre</th>
                <th>Capital {{ \App\Models\Setting::get('currency', 'USD') }}</th>
                <th>Taux %</th>
                <th>Durée</th>
                <th>Total dû {{ \App\Models\Setting::get('currency', 'USD') }}</th>
                <th>Restant {{ \App\Models\Setting::get('currency', 'USD') }}</th>
                <th>Statut</th>
                <th>Décaissement</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loans as $i => $loan)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $loan->member->full_name }}</td>
                <td style="text-align:right">{{ \App\Models\Setting::get('currency', 'USD') }} {{ number_format($loan->principal_amount, 2) }}</td>
                <td style="text-align:center">{{ $loan->interest_rate }}%</td>
                <td style="text-align:center">{{ $loan->term_months }} mois</td>
                <td style="text-align:right; font-weight:bold">{{ \App\Models\Setting::get('currency', 'USD') }} {{ number_format($loan->total_to_repay, 2) }}</td>
                <td style="text-align:right; color:{{ $loan->balance_remaining > 0 ? '#dc2626' : '#059669' }}">{{ \App\Models\Setting::get('currency', 'USD') }} {{ number_format($loan->balance_remaining, 2) }}</td>
                <td>
                    @php $colors = ['pending'=>'warning','active'=>'success','repaid'=>'gray','defaulted'=>'danger','rejected'=>'danger'] @endphp
                    <span class="badge badge-{{ $colors[$loan->status] ?? 'gray' }}">
                        {{ ['pending'=>'En attente','active'=>'En cours','repaid'=>'Remboursé','defaulted'=>'En défaut','rejected'=>'Rejeté'][$loan->status] ?? $loan->status }}
                    </span>
                </td>
                <td>{{ $loan->disbursement_date ? \Carbon\Carbon::parse($loan->disbursement_date)->format('d/m/Y') : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center; padding:15px; color:#9ca3af">Aucun prêt à afficher.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="footer">Mutulle — Rapport des Prêts — {{ $date }}</div>
</body>
</html>
