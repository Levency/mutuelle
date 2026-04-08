<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; }
        .header { background: #0891b2; color: white; padding: 15px 25px; margin-bottom: 15px; }
        .header h1 { font-size: 18px; }
        .header p { font-size: 10px; opacity: 0.85; }
        .content { padding: 0 25px 25px; }
        .summary { background: #ecfeff; border: 1px solid #a5f3fc; padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; }
        .summary span { font-weight: bold; color: #0891b2; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th { background: #0891b2; color: white; padding: 7px 8px; text-align: left; font-size: 10px; }
        tbody tr:nth-child(even) { background: #ecfeff; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #a5f3fc; }
        .footer { text-align: center; font-size: 9px; color: #9ca3af; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ $title }}</h1>
    <p>{{ $period_label }} | Généré le {{ $date }}</p>
</div>
<div class="content">
    <div class="summary">Total remboursé : <span>$ {{ number_format($total, 2) }}</span></div>
    <table>
        <thead>
            <tr><th>#</th><th>Membre</th><th>Prêt #</th><th>Montant ($)</th><th>Date</th><th>Notes</th></tr>
        </thead>
        <tbody>
            @forelse($repayments as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->loan->member->full_name }}</td>
                <td>#{{ $r->loan_id }}</td>
                <td style="text-align:right; font-weight:bold; color:#0891b2">$ {{ number_format($r->amount, 2) }}</td>

                <td>{{ \Carbon\Carbon::parse($r->payment_date)->format('d/m/Y') }}</td>
                <td>{{ $r->notes ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center; padding:15px; color:#9ca3af">Aucun remboursement sur cette période.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="footer">Mutulle — Rapport des Remboursements — {{ $date }}</div>
</body>
</html>
