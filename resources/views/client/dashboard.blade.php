@extends('layouts.front')

@section('title', 'Mon Tableau de Bord — Hello Passenger')

@push('styles')
    <script>window.tailwind=window.tailwind||{};window.tailwind.config={corePlugins:{preflight:false},important:'#client-page-root'};</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush

@section('content')
<div id="client-page-root" class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900" data-i18n="dashboard_welcome">Bienvenue, {{ $client->prenom ?? '' }} {{ $client->nom ?? '' }}</h1>
            <p class="text-gray-600 mt-2" data-i18n="dashboard_subtitle">Gérez vos réservations et votre compte</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <i class="fas fa-suitcase text-yellow-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600" data-i18n="dashboard_total_reservations">Total réservations</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalCommandes }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $commandesMois }} ce mois</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <i class="fas fa-euro-sign text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600" data-i18n="dashboard_total_spent">Total dépensé</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalDepense, 0, ',', ' ') }} €</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($depenseAujourdhui, 0, ',', ' ') }} € <span data-i18n="dashboard_today_label">aujourd'hui</span></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <i class="fas fa-check-circle text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600" data-i18n="dashboard_completed">Complétées</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $commandesCompleted }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $commandesPending }} <span data-i18n="dashboard_pending_label">en attente</span></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <i class="fas fa-camera text-purple-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600" data-i18n="dashboard_photos">Photos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalPhotos }}</p>
                        <p class="text-xs text-gray-500 mt-1" data-i18n="dashboard_my_bags">de mes bagages</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600" data-i18n="dashboard_today">Aujourd'hui</p>
                        <p class="text-2xl font-bold text-green-600">{{ $commandesAujourdhui }}</p>
                    </div>
                    <i class="fas fa-calendar-day text-3xl text-green-500"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600" data-i18n="dashboard_this_week">Cette semaine</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $commandesSemaine }}</p>
                    </div>
                    <i class="fas fa-calendar-week text-3xl text-blue-500"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600" data-i18n="dashboard_completion_rate">Taux de complétion</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $totalCommandes > 0 ? number_format(($commandesCompleted / $totalCommandes) * 100, 1) : 0 }}%</p>
                    </div>
                    <i class="fas fa-chart-pie text-3xl text-yellow-500"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <a href="{{ route('form-consigne') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg shadow-lg p-6 text-center transform transition-all hover:scale-105">
                <i class="fas fa-plus-circle text-4xl mb-3"></i>
                <h3 class="font-bold text-lg" data-i18n="dashboard_new_reservation">Nouvelle réservation</h3>
                <p class="text-sm mt-2 opacity-90" data-i18n="dashboard_book_now">Réserver maintenant</p>
            </a>
            <a href="{{ route('mes.reservations') }}" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-lg p-6 text-center transform transition-all hover:scale-105">
                <i class="fas fa-list text-4xl mb-3"></i>
                <h3 class="font-bold text-lg" data-i18n="dashboard_my_reservations">Mes réservations</h3>
                <p class="text-sm mt-2 opacity-90" data-i18n="dashboard_view_all">Voir toutes mes réservations</p>
            </a>
            <a href="{{ route('client.profile') }}" class="bg-green-500 hover:bg-green-600 text-white rounded-lg shadow-lg p-6 text-center transform transition-all hover:scale-105">
                <i class="fas fa-user-edit text-4xl mb-3"></i>
                <h3 class="font-bold text-lg" data-i18n="dashboard_edit_profile">Modifier profil</h3>
                <p class="text-sm mt-2 opacity-90" data-i18n="dashboard_update_info">Mettre à jour mes informations</p>
            </a>
            <a href="mailto:support@hellopassenger.com" class="bg-purple-500 hover:bg-purple-600 text-white rounded-lg shadow-lg p-6 text-center transform transition-all hover:scale-105">
                <i class="fas fa-question-circle text-4xl mb-3"></i>
                <h3 class="font-bold text-lg" data-i18n="dashboard_help">Aide & Support</h3>
                <p class="text-sm mt-2 opacity-90" data-i18n="dashboard_get_help">Obtenir de l'aide</p>
            </a>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900" data-i18n="dashboard_recent_reservations">Réservations récentes</h2>
            </div>
            <div class="p-6">
                @if($commandes->isEmpty())
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-gray-400 text-6xl mb-4"></i>
                        <p class="text-gray-600" data-i18n="dashboard_no_reservations">Vous n'avez pas encore de réservations</p>
                        <a href="{{ route('form-consigne') }}" class="mt-4 inline-block bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-bold transition-all" data-i18n="dashboard_create_first">Créer ma première réservation</a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-i18n="dashboard_order_ref">Référence</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-i18n="dashboard_products">Produits</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-i18n="dashboard_amount">Montant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-i18n="dashboard_status">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photos</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-i18n="dashboard_date">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-i18n="dashboard_actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($commandes as $commande)
                                    @php
                                        $details = is_array($commande->details_commande_lignes) ? $commande->details_commande_lignes : json_decode($commande->details_commande_lignes, true) ?? [];
                                        $produits = []; $dateDebut = null; $dateFin = null;
                                        foreach ($details as $item) {
                                            if (!empty($item['libelleProduit'])) $produits[] = $item['libelleProduit'];
                                            if (empty($dateDebut) && !empty($item['dateDebut'])) $dateDebut = $item['dateDebut'];
                                            if (empty($dateFin) && !empty($item['dateFin'])) $dateFin = $item['dateFin'];
                                        }
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $commande->getFormattedReference() }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <div class="max-w-xs">
                                                @if(!empty($produits))
                                                    @foreach(array_slice($produits, 0, 2) as $produit)<div class="truncate">{{ $produit }}</div>@endforeach
                                                    @if(count($produits) > 2)<div class="text-xs text-gray-400">+{{ count($produits) - 2 }} autre(s)</div>@endif
                                                @else<span class="text-gray-400">N/A</span>@endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($dateDebut && $dateFin)
                                                <div class="text-xs">
                                                    <div><i class="fas fa-calendar-check text-green-500 mr-1"></i>{{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y H:i') }}</div>
                                                    <div><i class="fas fa-calendar-times text-red-500 mr-1"></i>{{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y H:i') }}</div>
                                                </div>
                                            @else<span class="text-gray-400">N/A</span>@endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">{{ number_format($commande->total_prix_ttc, 2, ',', ' ') }} €</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php $statusKey = 'status_' . strtolower($commande->statut ?? 'pending'); $statusText = ucfirst($commande->statut ?? 'pending'); @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $commande->statut === 'completed' ? 'bg-green-100 text-green-800' : ($commande->statut === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}" data-i18n="{{ $statusKey }}">{{ $statusText }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="inline-flex items-center"><i class="fas fa-camera mr-1"></i>{{ $commande->photos ? $commande->photos->count() : 0 }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $commande->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('invoices.show', ['id' => $commande->id, 'lang' => session('app_language', 'fr')]) }}" target="_blank" class="text-yellow-600 hover:text-yellow-900" data-i18n="dashboard_view_invoice"><i class="fas fa-file-invoice mr-1"></i>Facture</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('mes.reservations') }}" class="text-yellow-600 hover:text-yellow-800 font-medium" data-i18n="dashboard_view_all_reservations">Voir toutes mes réservations →</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
