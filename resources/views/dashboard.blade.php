<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf_token" content="{{ csrf_token() }}">
    <title>Dashboard Admin - HelloPassenger</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @include('components.chatbot')
</head>
<body class="bg-gray-50">
  @include('components.header')
  <div class="flex">
    @include('components.sidebar')
    <main id="main-content" class="flex-1 p-6">
      <!-- Dashboard Admin Content -->
      <div class="space-y-6">
        <div class="flex items-center justify-between">
          <h1 class="text-3xl font-bold text-gray-900">Tableau de Bord Administrateur</h1>
          <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full border border-green-200 bg-green-50 text-green-700 text-sm">
              <span class="w-2 h-2 bg-green-500 rounded-full inline-block mr-2"></span>
              Système en ligne
            </span>
          </div>
        </div>

        <!-- Statistiques principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                <i class="fas fa-shopping-cart text-blue-600 text-2xl"></i>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Commandes</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalCommandes }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $commandesMois }} ce mois</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                <i class="fas fa-calendar-day text-green-600 text-2xl"></i>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Aujourd'hui</p>
                <p class="text-2xl font-bold text-gray-900">{{ $commandesAujourdhui }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $commandesSemaine }} cette semaine</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                <i class="fas fa-euro-sign text-yellow-600 text-2xl"></i>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Revenus Total</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalRevenue, 0, ',', ' ') }} €</p>
                <p class="text-xs text-gray-500 mt-1">{{ number_format($revenueAujourdhui, 0, ',', ' ') }} € aujourd'hui</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-orange-100 rounded-lg p-3">
                <i class="fas fa-clock text-orange-600 text-2xl"></i>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">En Attente</p>
                <p class="text-2xl font-bold text-gray-900">{{ $commandesEnAttente }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $commandesCompleted }} complétées</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Récapitulatif détaillé -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Complétées</p>
                <p class="text-2xl font-bold text-green-600">{{ $commandesCompleted }}</p>
              </div>
              <i class="fas fa-check-circle text-3xl text-green-500"></i>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Échouées</p>
                <p class="text-2xl font-bold text-red-600">{{ $commandesFailed }}</p>
              </div>
              <i class="fas fa-times-circle text-3xl text-red-500"></i>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Clients</p>
                <p class="text-2xl font-bold text-blue-600">{{ $totalClients }}</p>
              </div>
              <i class="fas fa-users text-3xl text-blue-500"></i>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Photos Uploadées</p>
                <p class="text-2xl font-bold text-purple-600">{{ $totalPhotos }}</p>
              </div>
              <i class="fas fa-camera text-3xl text-purple-500"></i>
            </div>
          </div>
        </div>

        <!-- Actions rapides -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <a href="{{ route('overview') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-all border-l-4 border-blue-500">
            <i class="fas fa-chart-line text-blue-500 text-3xl mb-3"></i>
            <h3 class="font-bold text-lg text-gray-900">Vue d'ensemble</h3>
            <p class="text-sm text-gray-600 mt-2">Statistiques et analyses</p>
          </a>

          <a href="{{ route('orders') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-all border-l-4 border-green-500">
            <i class="fas fa-box text-green-500 text-3xl mb-3"></i>
            <h3 class="font-bold text-lg text-gray-900">Commandes</h3>
            <p class="text-sm text-gray-600 mt-2">Gérer les commandes</p>
          </a>

          <a href="{{ route('users') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-all border-l-4 border-purple-500">
            <i class="fas fa-users text-purple-500 text-3xl mb-3"></i>
            <h3 class="font-bold text-lg text-gray-900">Utilisateurs</h3>
            <p class="text-sm text-gray-600 mt-2">Gérer les utilisateurs</p>
          </a>

          <a href="{{ route('analytics') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-all border-l-4 border-yellow-500">
            <i class="fas fa-chart-bar text-yellow-500 text-3xl mb-3"></i>
            <h3 class="font-bold text-lg text-gray-900">Analytiques</h3>
            <p class="text-sm text-gray-600 mt-2">Analyses détaillées</p>
          </a>
        </div>

        <!-- Commandes récentes -->
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Commandes récentes</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produits</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Photos</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                @forelse($recentCommandes as $commande)
                  @php
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
                      {{ $commande->getFormattedReference() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ $commande->client_prenom ?? 'N/A' }} {{ $commande->client_nom ?? '' }}
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                      {{ number_format($commande->total_prix_ttc, 2, ',', ' ') }} €
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      @php
                        $s = strtolower($commande->statut ?? 'pending');
                        $badge = match ($s) {
                          'completed' => 'bg-green-100 text-green-800',
                          'failed' => 'bg-red-100 text-red-800',
                          'cancelled' => 'bg-gray-100 text-gray-800',
                          'processing' => 'bg-blue-100 text-blue-800',
                          default => 'bg-yellow-100 text-yellow-800',
                        };
                      @endphp
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badge }}">
                        {{ ucfirst($s) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <span class="inline-flex items-center">
                        <i class="fas fa-camera mr-1"></i>
                        {{ $commande->photos ? $commande->photos->count() : 0 }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ $commande->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      @if(session('user_role') === 'admin')
                        <form method="POST" action="{{ route('admin.commandes.status', $commande->id) }}" class="flex items-center gap-2">
                          @csrf
                          <select name="statut" class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <option value="pending" {{ ($commande->statut ?? 'pending') === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="processing" {{ ($commande->statut ?? '') === 'processing' ? 'selected' : '' }}>En cours</option>
                            <option value="completed" {{ ($commande->statut ?? '') === 'completed' ? 'selected' : '' }}>Terminé</option>
                            <option value="cancelled" {{ ($commande->statut ?? '') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                            <option value="failed" {{ ($commande->statut ?? '') === 'failed' ? 'selected' : '' }}>Échoué</option>
                          </select>
                          <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm font-semibold">
                            Mettre à jour
                          </button>
                        </form>
                      @else
                        <span class="text-gray-400">—</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="10" class="px-6 py-4 text-center text-gray-500">Aucune commande récente</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @yield('content')
    </main>
  </div>
</body>
</html>
