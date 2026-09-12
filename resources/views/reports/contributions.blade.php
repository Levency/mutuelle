<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; }
        .header { background: #059669; color: white; padding: 15px 25px; margin-bottom: 15px; }
        .header h1 { font-size: 18px; }
        .header p { font-size: 10px; opacity: 0.85; }
        .content { padding: 0 25px 25px; }
        .summary { background: #ecfdf5; border: 1px solid #6ee7b7; padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; }
        .summary span { font-weight: bold; color: #059669; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th { background: #059669; color: white; padding: 7px 8px; text-align: left; font-size: 10px; }
        tbody tr:nth-child(even) { background: #f0fdf4; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #d1fae5; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-success { background: #d1fae5; color: #059669; }
        .badge-danger  { background: #fee2e2; color: #dc2626; }
        .badge-warning { background: #fef3c7; color: #d97706; }
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
        Total encaissé : <span>{{ \App\Models\Setting::get('currency', 'Gourdes') }} {{ number_format($total, 2) }}</span> &nbsp;|&nbsp;
        Cotisations en retard : <span style="color:#dc2626">{{ $late_count }}</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>N° Reçu</th>
                <th>Membre</th>
                <th>N° Membre</th>
                <th>Montant {{ \App\Models\Setting::get('currency', 'Gourdes') }}</th>
                <th>Date</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contributions as $i => $c)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $c->receipt_number ?? '—' }}</td>
                <td>{{ $c->member->full_name }}</td>
                <td>{{ $c->member->member_number }}</td>
                <td style="text-align:right; font-weight:bold">{{ \App\Models\Setting::get('currency', 'Gourdes') }} {{ number_format($c->amount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($c->payment_date)->format('d/m/Y') }}</td>
                <td>
                    @if($c->status === 'paid')
                        <span class="badge badge-success">Payé</span>
                    @elseif($c->status === 'late')
                        <span class="badge badge-danger">En retard</span>
                    @else
                        <span class="badge badge-warning">En attente</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center; padding:15px; color:#9ca3af">Aucune cotisation sur cette période.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="footer">Mutulle — Rapport des Cotisations — {{ $date }}</div>
</body>
</html>
