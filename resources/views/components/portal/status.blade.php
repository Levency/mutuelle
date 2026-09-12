@props(['type', 'status'])

@php
    [$label, $tone] = match ($type) {
        'contribution' => match ($status) {
            'paid'    => ['Payé', 'success'],
            'late'    => ['En retard', 'danger'],
            default   => ['En attente', 'warning'],
        },
        'loan' => match ($status) {
            'active'    => ['En cours', 'success'],
            'repaid'    => ['Remboursé', 'success'],
            'defaulted' => ['En défaut', 'danger'],
            'rejected'  => ['Rejeté', 'danger'],
            default     => ['En attente', 'warning'],
        },
        'help' => match ($status) {
            'validated' => ['Validée', 'success'],
            'paid'      => ['Payée', 'success'],
            'rejected'  => ['Rejetée', 'danger'],
            default     => ['En attente', 'warning'],
        },
        'schedule' => match ($status) {
            'paid'    => ['Payée', 'success'],
            'partial' => ['Partielle', 'warning'],
            default   => ['À échoir', 'neutral'],
        },
        'penalty' => match ($status) {
            'paid'   => ['Payée', 'success'],
            'waived' => ['Annulée', 'neutral'],
            default  => ['En attente', 'danger'],
        },
        default => [ucfirst((string) $status), 'neutral'],
    };
@endphp

<x-portal.stamp :tone="$tone">{{ $label }}</x-portal.stamp>
