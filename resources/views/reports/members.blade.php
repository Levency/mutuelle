<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; }
        .header { background: #4b5563; color: white; padding: 15px 25px; margin-bottom: 15px; }
        .header h1 { font-size: 18px; }
        .header p { font-size: 10px; opacity: 0.85; }
        .content { padding: 0 25px 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th { background: #4b5563; color: white; padding: 7px 8px; text-align: left; font-size: 10px; }
        tbody tr:nth-child(even) { background: #f3f4f6; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-success { background: #d1fae5; color: #059669; }
        .badge-danger  { background: #fee2e2; color: #dc2626; }
        .footer { text-align: center; font-size: 9px; color: #9ca3af; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ $title }}</h1>
    <p>{{ $period_label }} | Généré le {{ $date }}</p>
</div>
<div class="content">
    <table>
        <thead>
            <tr>
                <th>N° Membre</th>
                <th>Nom Complet</th>
                <th>Téléphone</th>
                <th>Adhésion</th>
                <th>Statut</th>
                <th>Score</th>
                <th>Cotis. (Nombre)</th>
                <th>Total Cotisé {{ \App\Models\Setting::get('currency', 'USD') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($members as $m)
            <tr>
                <td style="font-weight:bold">{{ $m->member_number }}</td>
                <td>{{ $m->full_name }}</td>
                <td>{{ $m->phone ?? '—' }}</td>
                <td>{{ $m->joined_at->format('d/m/Y') }}</td>
                <td>
                    <span class="badge {{ $m->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                        {{ $m->status === 'active' ? 'Actif' : 'Suspendu' }}
                    </span>
                </td>
                <td style="text-align:center">{{ $m->confidence_score }}%</td>
                <td style="text-align:center">{{ $m->contributions_count }}</td>
                <td style="text-align:right; font-weight:bold">{{ \App\Models\Setting::get('currency', 'USD') }} {{ number_format($m->contributions_sum_amount ?? 0, 2) }}</td>

            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="footer">Mutulle — Rapport des Membres — {{ $date }}</div>
</body>
</html>
