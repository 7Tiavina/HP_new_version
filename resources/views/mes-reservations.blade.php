@extends('layouts.front')

@section('title', 'Mes Réservations — Hello Passenger')

@push('styles')
    <script>window.tailwind=window.tailwind||{};window.tailwind.config={corePlugins:{preflight:false},important:'#client-page-root'};</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #client-page-root {
            font-family: 'Space Grotesk', sans-serif !important;
        }
    </style>
@endpush

@section('content')
<div id="client-page-root" class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900" data-i18n="reservations_title">Mes Réservations</h1>
                <p class="text-gray-600 mt-2" data-i18n="reservations_history">Consultez l'historique de vos réservations</p>
            </div>
            <a href="{{ route('client.dashboard') }}" class="flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors" data-i18n="btn_back">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour
            </a>
        </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold" data-i18n="reservations_success">Succès!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white p-6 rounded-lg shadow-md">
            @if ($commandes->isEmpty())
                <p class="text-gray-600" data-i18n="reservations_empty">Vous n'avez pas encore de commandes.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider" data-i18n="reservations_order_id">ID Commande</th>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Produits</th>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Dates</th>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider" data-i18n="reservations_total">Total TTC</th>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider" data-i18n="reservations_status">Statut</th>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider" data-i18n="reservations_date">Date</th>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($commandes as $commande)
                                @php
                                    if (!$commande->relationLoaded('photos')) { $commande->load('photos'); }
                                    $photosCount = $commande->photos ? $commande->photos->count() : 0;
                                    $baseRef = $commande->id_api_commande ?? $commande->id;
                                    $orlyAirportId = '64f00ace-31b6-45b0-bcb2-b562b1ac08d9';
                                    $cdgAirportId = '88bb89e0-b966-4420-9ed3-7a6745e4d947';
                                    $airportId = $commande->id_plateforme ?? null;
                                    if ($airportId === $orlyAirportId) { $commandeRef = 'F-ORY-' . $baseRef; }
                                    elseif ($airportId === $cdgAirportId) { $commandeRef = 'F-CDG-' . $baseRef; }
                                    else { $commandeRef = $baseRef; }
                                    $details = is_array($commande->details_commande_lignes) ? $commande->details_commande_lignes : json_decode($commande->details_commande_lignes, true) ?? [];
                                    $produits = []; $dateDebut = null; $dateFin = null;
                                    foreach ($details as $item) {
                                        if (!empty($item['libelleProduit'])) $produits[] = $item['libelleProduit'];
                                        if (empty($dateDebut) && !empty($item['dateDebut'])) $dateDebut = $item['dateDebut'];
                                        if (empty($dateFin) && !empty($item['dateFin'])) $dateFin = $item['dateFin'];
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50" data-commande-id="{{ $commande->id }}">
                                    <td class="py-2 px-4 border-b border-gray-200 text-sm font-medium text-gray-900">{{ $commandeRef }}</td>
                                    <td class="py-2 px-4 border-b border-gray-200 text-sm text-gray-500">
                                        <div class="max-w-xs">
                                            @if(!empty($produits))
                                                @foreach(array_slice($produits, 0, 2) as $produit)<div class="truncate">{{ $produit }}</div>@endforeach
                                                @if(count($produits) > 2)<div class="text-xs text-gray-400">+{{ count($produits) - 2 }} autre(s)</div>@endif
                                            @else<span class="text-gray-400">N/A</span>@endif
                                        </div>
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-200 text-sm text-gray-500">
                                        @if($dateDebut && $dateFin)
                                            <div class="text-xs">
                                                <div><i class="fas fa-calendar-check text-green-500 mr-1"></i>{{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y H:i') }}</div>
                                                <div><i class="fas fa-calendar-times text-red-500 mr-1"></i>{{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y H:i') }}</div>
                                            </div>
                                        @else<span class="text-gray-400">N/A</span>@endif
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-200 text-sm font-semibold text-gray-900">{{ number_format($commande->total_prix_ttc, 2) }} €</td>
                                    <td class="py-2 px-4 border-b border-gray-200 text-sm text-gray-900">
                                        @php $statusKey = 'status_' . strtolower($commande->statut ?? 'pending'); $statusText = ucfirst($commande->statut ?? 'pending'); @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $commande->statut == 'completed' ? 'bg-green-100 text-green-800' : ($commande->statut == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}" data-i18n="{{ $statusKey }}">{{ $statusText }}</span>
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-200 text-sm text-gray-500">{{ $commande->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-2 px-4 border-b border-gray-200 text-sm text-gray-900">
                                        <div class="flex gap-2">
                                            <a href="{{ route('invoices.show', ['id' => $commande->id, 'lang' => session('app_language', 'fr')]) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900" data-i18n="dashboard_view_invoice">Voir facture</a>
                                            @if($photosCount > 0)
                                                <a href="#" onclick="showPhotos({{ $commande->id }}); return false;" class="text-green-600 hover:text-green-800 cursor-pointer"><i class="fas fa-camera"></i> {{ $photosCount }} photo(s)</a>
                                            @else<span class="text-gray-400 text-xs">Aucune photo</span>@endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $commandes->links('vendor.pagination.custom') }}
                </div>
            @endif
            </div>
        </div>
    </div>
</div>

{{-- Photos Modal - Outside main container --}}
<div id="photosModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center" style="display: none;">
                    <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-bold">Photos des Bagages</h2>
                            <button onclick="closePhotosModal()" class="text-gray-600 hover:text-gray-800"><i class="fas fa-times text-2xl"></i></button>
                        </div>
                        <div id="photosContent" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                    </div>
                </div>
@endsection

@push('scripts')
<script>
function showPhotos(commandeId) {
    var content = document.getElementById('photosContent');
    content.innerHTML = '<p class="text-gray-600 text-center py-8">Chargement des photos...</p>';
    document.getElementById('photosModal').style.display = 'flex';
    document.getElementById('photosModal').classList.remove('hidden');
    fetch('/mes-reservations/photos/' + commandeId, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(function(data) {
            content.innerHTML = '';
            if (data.photos && data.photos.length > 0) {
                data.photos.forEach(function(photo) {
                    var div = document.createElement('div');
                    div.className = 'border rounded-lg p-4';
                    div.innerHTML = '<img src="' + photo.url + '" alt="Photo ' + photo.type + '" class="w-full h-64 object-cover rounded mb-2">' +
                        '<p class="font-bold">' + (photo.type === 'depot' ? 'Dépôt' : 'Restitution') + '</p>' +
                        '<p class="text-gray-600 text-sm">' + photo.created_at + '</p>';
                    content.appendChild(div);
                });
            } else {
                content.innerHTML = '<p class="text-gray-600 text-center py-8">Aucune photo disponible pour cette commande</p>';
            }
        })
        .catch(function() {
            content.innerHTML = '<p class="text-red-600 text-center py-8">Erreur lors du chargement des photos.</p>';
        });
}
function closePhotosModal() {
    document.getElementById('photosModal').style.display = 'none';
    document.getElementById('photosModal').classList.add('hidden');
}
</script>
@endpush
