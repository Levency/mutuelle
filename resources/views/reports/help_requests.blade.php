<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; }
        .header { background: #d97706; color: white; padding: 15px 25px; margin-bottom: 15px; }
        .header h1 { font-size: 18px; }
        .header p { font-size: 10px; opacity: 0.85; }
        .content { padding: 0 25px 25px; }
        .summary { background: #fffbeb; border: 1px solid #fde68a; padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; }
        .summary span { font-weight: bold; color: #d97706; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th { background: #d97706; color: white; padding: 7px 8px; text-align: left; font-size: 10px; }
        tbody tr:nth-child(even) { background: #fffbeb; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #fde68a; }
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
        Montant total approuvé : <span>{{ \App\Models\Setting::get('currency', 'Gourdes') }} {{ number_format($total_approved, 2) }}</span> &nbsp;|&nbsp;
        En attente : <span style="color:#dc2626">{{ $pending_count }}</span>
    </div>
    <table>
        <thead>
            <tr><th>#</th><th>Membre</th><th>Motif</th><th>Montant {{ \App\Models\Setting::get('currency', 'Gourdes') }}</th><th>Statut</th><th>Date</th></tr>
        </thead>
        <tbody>
            @forelse($helpRequests as $i => $hr)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $hr->member->full_name }}</td>
                <td>{{ $hr->reason ?? '—' }}</td>
                <td style="text-align:right; font-weight:bold">{{ \App\Models\Setting::get('currency', 'Gourdes') }} {{ number_format($hr->amount, 2) }}</td>

                <td>
                    @if($hr->status === 'approved')
                        <span class="badge badge-success">Approuvé</span>
                    @elseif($hr->status === 'rejected')
                        <span class="badge badge-danger">Rejeté</span>
                    @else
                        <span class="badge badge-warning">En attente</span>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($hr->created_at)->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center; padding:15px; color:#9ca3af">Aucune demande à afficher.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="footer">Mutulle — Rapport des Demandes d'Aide — {{ $date }}</div>
</body>
</html>
