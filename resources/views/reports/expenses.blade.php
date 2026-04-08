<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; }
        .header { background: #ea580c; color: white; padding: 15px 25px; margin-bottom: 15px; }
        .header h1 { font-size: 18px; }
        .header p { font-size: 10px; opacity: 0.85; }
        .content { padding: 0 25px 25px; }
        .summary { background: #fff7ed; border: 1px solid #ffedd5; padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; }
        .summary span { font-weight: bold; color: #ea580c; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th { background: #ea580c; color: white; padding: 8px; text-align: left; font-size: 11px; }
        tbody td { padding: 8px; border-bottom: 1px solid #fed7aa; }
        tbody tr:nth-child(even) { background: #fff7ed; }
        .footer { text-align: center; font-size: 9px; color: #9ca3af; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ $title }}</h1>
    <p>{{ $period_label }} | Généré le {{ $date }}</p>
</div>
<div class="content">
    <div class="summary">Total des dépenses sur la période : <span>$ {{ number_format($total, 2) }}</span></div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description / Motif</th>
                <th>Montant ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $e)
            <tr>
                <td>{{ $e->created_at->format('d/m/Y') }}</td>
                <td>{{ $e->description }}</td>
                <td style="text-align:right; font-weight:bold">$ {{ number_format($e->amount, 2) }}</td>

            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center; padding:20px; color:#9ca3af">Aucune dépense enregistrée sur cette période.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="footer">Mutulle — Journal des Dépenses — {{ $date }}</div>
</body>
</html>
