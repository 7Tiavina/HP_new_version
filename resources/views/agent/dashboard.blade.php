<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Agent - HelloPassenger</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @include('components.chatbot')
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-yellow-400 shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Agent</h1>
            <div class="flex items-center gap-4">
                <span class="text-gray-700">Agent: {{ session('agent_email') }}</span>
                <form method="POST" action="{{ route('agent.logout') }}">
                    @csrf
                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">
                        <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Commandes</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_commandes'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $stats['commandes_mois'] }} ce mois</p>
                    </div>
                    <i class="fas fa-shopping-cart text-4xl text-blue-500"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Aujourd'hui</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['commandes_aujourdhui'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $stats['commandes_semaine'] }} cette semaine</p>
                    </div>
                    <i class="fas fa-calendar-day text-4xl text-green-500"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Revenus Total</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['revenue_total'], 0, ',', ' ') }} €</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($stats['revenue_aujourdhui'], 0, ',', ' ') }} € aujourd'hui</p>
                    </div>
                    <i class="fas fa-euro-sign text-4xl text-yellow-500"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Photos Prises</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_photos'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $stats['photos_depot'] }} dépôt / {{ $stats['photos_restitution'] }} restitution</p>
                    </div>
                    <i class="fas fa-camera text-4xl text-purple-500"></i>
                </div>
            </div>
        </div>

        <!-- Récapitulatif par statut -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Commandes Complétées</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['commandes_completed'] }}</p>
                    </div>
                    <i class="fas fa-check-circle text-3xl text-green-500"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">En Attente</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['commandes_pending'] }}</p>
                    </div>
                    <i class="fas fa-clock text-3xl text-yellow-500"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Taux de Complétion</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ $stats['total_commandes'] > 0 ? number_format(($stats['commandes_completed'] / $stats['total_commandes']) * 100, 1) : 0 }}%
                        </p>
                    </div>
                    <i class="fas fa-chart-pie text-3xl text-blue-500"></i>
                </div>
            </div>
        </div>

        <!-- Commandes List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Liste des Commandes</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produits</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($commandes as $commande)
                            @php
                                $baseRef = $commande->id_api_commande ?? $commande->id;
                                $orlyAirportId = '64f00ace-31b6-45b0-bcb2-b562b1ac08d9';
                                $cdgAirportId = '88bb89e0-b966-4420-9ed3-7a6745e4d947';
                                $airportId = $commande->id_plateforme ?? null;
                                
                                if ($airportId === $orlyAirportId) {
                                    $commandeRef = 'F-ORY-' . $baseRef;
                                } elseif ($airportId === $cdgAirportId) {
                                    $commandeRef = 'F-CDG-' . $baseRef;
                                } else {
                                    $commandeRef = $baseRef;
                                }
                                
                                // Détails de la commande
                                $details = is_array($commande->details_commande_lignes) ? $commande->details_commande_lignes : json_decode($commande->details_commande_lignes, true) ?? [];
                                $produits = [];
                                $dateDebut = null;
                                $dateFin = null;
                                
                                foreach ($details as $item) {
                                    if (!empty($item['libelleProduit'])) {
                                        $produits[] = $item['libelleProduit'];
                                    }
                                    if (empty($dateDebut) && !empty($item['dateDebut'])) {
                                        $dateDebut = $item['dateDebut'];
                                    }
                                    if (empty($dateFin) && !empty($item['dateFin'])) {
                                        $dateFin = $item['dateFin'];
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $commandeRef }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $commande->client_prenom }} {{ $commande->client_nom }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $commande->client_email }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="max-w-xs">
                                        @if(!empty($produits))
                                            @foreach(array_slice($produits, 0, 2) as $produit)
                                                <div class="truncate">{{ $produit }}</div>
                                            @endforeach
                                            @if(count($produits) > 2)
                                                <div class="text-xs text-gray-400">+{{ count($produits) - 2 }} autre(s)</div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($dateDebut && $dateFin)
                                        <div class="text-xs">
                                            <div><i class="fas fa-calendar-check text-green-500 mr-1"></i>{{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y H:i') }}</div>
                                            <div><i class="fas fa-calendar-times text-red-500 mr-1"></i>{{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y H:i') }}</div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ number_format($commande->total_prix_ttc, 2) }} €
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $commande->statut == 'completed' ? 'bg-green-100 text-green-800' : ($commande->statut == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $commande->statut }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center">
                                        <i class="fas fa-camera mr-1"></i>
                                        {{ $commande->photos->count() }} photo(s)
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $commande->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('agent.commande.show', $commande->id) }}" 
                                       class="text-yellow-600 hover:text-yellow-900">
                                        <i class="fas fa-eye mr-1"></i>Voir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
