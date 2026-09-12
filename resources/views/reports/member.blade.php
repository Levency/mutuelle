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

        .identity { display: table; width: 100%; margin-bottom: 15px; border: 1px solid #e5e7eb; border-radius: 4px; }
        .identity .cell { display: table-cell; padding: 8px 12px; border-right: 1px solid #e5e7eb; }
        .identity .cell:last-child { border-right: none; }
        .identity .label { font-size: 8px; text-transform: uppercase; color: #6b7280; }
        .identity .value { font-size: 12px; font-weight: bold; margin-top: 2px; }

        .summary { display: table; width: 100%; margin-bottom: 18px; }
        .summary .box { display: table-cell; width: 20%; padding: 10px; border: 1px solid #e5e7eb; text-align: center; }
        .summary .box .label { font-size: 8px; text-transform: uppercase; color: #6b7280; }
        .summary .box .value { font-size: 13px; font-weight: bold; margin-top: 3px; }

        h2.section { font-size: 12px; background: #eef2ff; color: #3730a3; padding: 6px 8px; margin: 18px 0 6px; border-left: 3px solid #4f46e5; }

        table { width: 100%; border-collapse: collapse; }
        thead th { background: #4b5563; color: white; padding: 6px 8px; text-align: left; font-size: 9px; }
        tbody tr:nth-child(even) { background: #f3f4f6; }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        .empty { padding: 10px 8px; color: #9ca3af; font-style: italic; }

        .badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 8px; font-weight: bold; }
        .badge-success { background: #d1fae5; color: #059669; }
        .badge-danger  { background: #fee2e2; color: #dc2626; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-info    { background: #dbeafe; color: #1d4ed8; }
        .badge-gray    { background: #e5e7eb; color: #374151; }

        .text-right { text-align: right; }
        .footer { text-align: center; font-size: 9px; color: #9ca3af; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ $title }}</h1>
    <p>Historique complet, sans exception | Généré le {{ $date }}</p>
</div>
<div class="content">

    <div class="identity">
        <div class="cell">
            <div class="label">N° Membre</div>
            <div class="value">{{ $member->member_number }}</div>
        </div>
        <div class="cell">
            <div class="label">Statut</div>
            <div class="value">
                <span class="badge {{ $member->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                    {{ match($member->status) { 'active' => 'Actif', 'suspended' => 'Suspendu', default => 'Inactif' } }}
                </span>
            </div>
        </div>
        <div class="cell">
            <div class="label">Téléphone</div>
            <div class="value">{{ $member->phone ?? '—' }}</div>
        </div>
        <div class="cell">
            <div class="label">Adhésion</div>
            <div class="value">{{ $member->joined_at?->format('d/m/Y') ?? '—' }}</div>
        </div>
        <div class="cell">
            <div class="label">Score de confiance</div>
            <div class="value">{{ $member->confidence_score }}%</div>
        </div>
    </div>

    <div class="summary">
        <div class="box">
            <div class="label">Total cotisé</div>
            <div class="value">{{ number_format($totalContributions, 2) }} {{ $currency }}</div>
        </div>
        <div class="box">
            <div class="label">Total remboursé</div>
            <div class="value">{{ number_format($totalRepayments, 2) }} {{ $currency }}</div>
        </div>
        <div class="box">
            <div class="label">Aides reçues</div>
            <div class="value">{{ number_format($totalHelpPaid, 2) }} {{ $currency }}</div>
        </div>
        <div class="box">
            <div class="label">Contribution solidarité</div>
            <div class="value">{{ number_format($totalSolidarity, 2) }} {{ $currency }}</div>
        </div>
        <div class="box">
            <div class="label">Prêts (total)</div>
            <div class="value">{{ $loans->count() }}</div>
        </div>
    </div>

    <!-- ── Cotisations ─────────────────────────────────────────────────── -->
    <h2 class="section">Cotisations ({{ $contributions->count() }})</h2>
    <table>
        <thead>
            <tr><th>Date</th><th>N° Reçu</th><th>Montant</th><th>Statut</th><th>Notes</th></tr>
        </thead>
        <tbody>
            @forelse($contributions as $c)
                <tr>
                    <td>{{ $c->payment_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $c->receipt_number ?? '—' }}</td>
                    <td class="text-right">{{ number_format($c->amount, 2) }} {{ $currency }}</td>
                    <td>
                        <span class="badge {{ match($c->status) { 'paid' => 'badge-success', 'late' => 'badge-danger', default => 'badge-warning' } }}">
                            {{ match($c->status) { 'paid' => 'Payé', 'late' => 'En retard', default => 'En attente' } }}
                        </span>
                    </td>
                    <td>{{ $c->notes ?? '—' }}</td>
                </tr>
            @empty
                <tr><td class="empty" colspan="5">Aucune cotisation enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- ── Prêts ───────────────────────────────────────────────────────── -->
    <h2 class="section">Prêts ({{ $loans->count() }})</h2>
    <table>
        <thead>
            <tr><th>Date</th><th>Principal</th><th>Taux</th><th>Durée</th><th>Total à rembourser</th><th>Solde restant</th><th>Statut</th></tr>
        </thead>
        <tbody>
            @forelse($loans as $l)
                <tr>
                    <td>{{ $l->disbursement_date?->format('d/m/Y') ?? $l->created_at->format('d/m/Y') }}</td>
                    <td class="text-right">{{ number_format($l->principal_amount, 2) }} {{ $currency }}</td>
                    <td>{{ number_format($l->interest_rate, 2) }}%</td>
                    <td>{{ $l->term_months }} mois</td>
                    <td class="text-right">{{ number_format($l->total_to_repay, 2) }} {{ $currency }}</td>
                    <td class="text-right">{{ number_format($l->balance_remaining, 2) }} {{ $currency }}</td>
                    <td>
                        <span class="badge {{ match($l->status) { 'active' => 'badge-info', 'repaid' => 'badge-success', 'defaulted', 'rejected' => 'badge-danger', default => 'badge-warning' } }}">
                            {{ match($l->status) { 'pending' => 'En attente', 'active' => 'En cours', 'repaid' => 'Remboursé', 'defaulted' => 'En défaut', 'rejected' => 'Rejeté', default => $l->status } }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td class="empty" colspan="7">Aucun prêt enregistré.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- ── Remboursements ──────────────────────────────────────────────── -->
    <h2 class="section">Remboursements ({{ $repayments->count() }})</h2>
    <table>
        <thead>
            <tr><th>Date</th><th>Prêt</th><th>Montant payé</th><th>Dont capital</th><th>Dont intérêt</th><th>Moyen</th><th>N° Reçu</th></tr>
        </thead>
        <tbody>
            @forelse($repayments as $r)
                <tr>
                    <td>{{ $r->payment_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>#{{ $r->loan_id }}</td>
                    <td class="text-right">{{ number_format($r->amount_paid, 2) }} {{ $currency }}</td>
                    <td class="text-right">{{ number_format($r->principal_paid, 2) }} {{ $currency }}</td>
                    <td class="text-right">{{ number_format($r->interest_paid, 2) }} {{ $currency }}</td>
                    <td>{{ $r->payment_method ?? '—' }}</td>
                    <td>{{ $r->receipt_number ?? '—' }}</td>
                </tr>
            @empty
                <tr><td class="empty" colspan="7">Aucun remboursement enregistré.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- ── Demandes d'Aide ─────────────────────────────────────────────── -->
    <h2 class="section">Demandes d'Aide ({{ $helpRequests->count() }})</h2>
    <table>
        <thead>
            <tr><th>Date</th><th>Motif</th><th>Montant demandé</th><th>Statut</th></tr>
        </thead>
        <tbody>
            @forelse($helpRequests as $h)
                <tr>
                    <td>{{ $h->created_at->format('d/m/Y') }}</td>
                    <td>{{ $h->reason }}</td>
                    <td class="text-right">{{ number_format($h->amount_requested, 2) }} {{ $currency }}</td>
                    <td>
                        <span class="badge {{ match($h->status) { 'validated', 'paid' => 'badge-success', 'rejected' => 'badge-danger', default => 'badge-warning' } }}">
                            {{ match($h->status) { 'pending' => 'En attente', 'validated' => 'Validée', 'paid' => 'Payée', 'rejected' => 'Rejetée', default => $h->status } }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td class="empty" colspan="4">Aucune demande d'aide enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- ── Fonds de Solidarité ─────────────────────────────────────────── -->
    <h2 class="section">Fonds de Solidarité ({{ $solidarity->count() }})</h2>
    <table>
        <thead>
            <tr><th>Date</th><th>Type</th><th>Montant</th><th>Description</th></tr>
        </thead>
        <tbody>
            @forelse($solidarity as $s)
                <tr>
                    <td>{{ $s->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge {{ $s->type === 'inflow' ? 'badge-success' : 'badge-danger' }}">
                            {{ $s->type === 'inflow' ? 'Entrée' : 'Sortie' }}
                        </span>
                    </td>
                    <td class="text-right">{{ number_format($s->amount, 2) }} {{ $currency }}</td>
                    <td>{{ $s->description }}</td>
                </tr>
            @empty
                <tr><td class="empty" colspan="4">Aucun mouvement de solidarité enregistré.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($contribPenalties->isNotEmpty() || $loanPenalties->isNotEmpty())
        <!-- ── Pénalités ────────────────────────────────────────────────── -->
        <h2 class="section">Pénalités de Retard ({{ $contribPenalties->count() + $loanPenalties->count() }})</h2>
        <table>
            <thead>
                <tr><th>Date</th><th>Type</th><th>Périodes de retard</th><th>Montant</th><th>Statut</th></tr>
            </thead>
            <tbody>
                @foreach($contribPenalties as $p)
                    <tr>
                        <td>{{ $p->created_at->format('d/m/Y') }}</td>
                        <td>Cotisation</td>
                        <td>{{ $p->periods_late }}</td>
                        <td class="text-right">{{ number_format($p->amount, 2) }} {{ $currency }}</td>
                        <td>
                            <span class="badge {{ match($p->status) { 'paid' => 'badge-success', 'waived' => 'badge-gray', default => 'badge-danger' } }}">
                                {{ match($p->status) { 'paid' => 'Payée', 'waived' => 'Annulée', default => 'En attente' } }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                @foreach($loanPenalties as $p)
                    <tr>
                        <td>{{ $p->created_at->format('d/m/Y') }}</td>
                        <td>Prêt #{{ $p->loan_id }}</td>
                        <td>{{ $p->periods_late }}</td>
                        <td class="text-right">{{ number_format($p->amount, 2) }} {{ $currency }}</td>
                        <td>
                            <span class="badge {{ match($p->status) { 'paid' => 'badge-success', 'waived' => 'badge-gray', default => 'badge-danger' } }}">
                                {{ match($p->status) { 'paid' => 'Payée', 'waived' => 'Annulée', default => 'En attente' } }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</div>
<div class="footer">{{ \App\Models\Setting::get('organization_name', 'Mutuelle') }} — Rapport complet du membre {{ $member->member_number }} — {{ $date }}</div>
</body>
</html>
