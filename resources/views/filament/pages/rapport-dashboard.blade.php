<x-filament-panels::page>
    <x-filament-panels::form>
        {{ $this->form }}
    </x-filament-panels::form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        {{-- Macro pour créer une carte de rapport --}}
        @php
            $makeCard = function($type, $title, $description, $icon, $color) {
                return [
                    'type' => $type,
                    'title' => $title,
                    'description' => $description,
                    'icon' => $icon,
                    'color' => $color
                ];
            };

            $cards = [
                $makeCard('general', 'Rapport Général', 'Résumé complet de la situation financière.', 'heroicon-o-presentation-chart-line', 'primary'),
                $makeCard('balance', 'Audit Solde Disponible', 'Analyse détaillée du solde et marges.', 'heroicon-o-calculator', 'indigo'),
                $makeCard('members', 'Rapport des Membres', 'Liste exhaustive et totaux cotisés.', 'heroicon-o-user-group', 'gray'),
                $makeCard('contributions', 'Cotisations', 'Détail des entrées et suivi des retards.', 'heroicon-o-banknotes', 'success'),
                $makeCard('loans', 'Suivi des Prêts', 'Liste des prêts actifs et encours.', 'heroicon-o-credit-card', 'info'),
                $makeCard('expenses', 'Journal des Dépenses', 'Historique des sorties de fonds.', 'heroicon-o-shopping-cart', 'orange'),
                $makeCard('repayments', 'Remboursements', 'Historique des paiements reçus.', 'heroicon-o-arrow-path', 'warning'),
                $makeCard('help_requests', 'Demandes d\'Aide', 'Journal des aides accordées.', 'heroicon-o-heart', 'danger'),
            ];
        @endphp

        @foreach($cards as $card)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-dynamic-component 
                            :component="$card['icon']" 
                            class="w-6 h-6 text-{{ $card['color'] }}-600"
                        />
                        <span>{{ $card['title'] }}</span>
                    </div>
                </x-slot>
                
                <p class="text-sm text-gray-500 mb-4">{{ $card['description'] }}</p>
                
                <div class="flex gap-2">
                    <x-filament::button 
                        wire:click="generateReport('{{ $card['type'] }}')" 
                        wire:loading.attr="disabled"
                        icon="heroicon-o-eye" 
                        size="sm" 
                        :color="$card['color']">
                        Consulter
                    </x-filament::button>
                    
                    <x-filament::button 
                        wire:click="generateReport('{{ $card['type'] }}', 'download')" 
                        wire:loading.attr="disabled"
                        icon="heroicon-o-arrow-down-tray" 
                        size="sm" 
                        color="gray">
                        PDF
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endforeach


    </div>

    <script>
        window.addEventListener('open-report', event => {
            window.open(event.detail.url, '_blank');
        });
    </script>
</x-filament-panels::page>
