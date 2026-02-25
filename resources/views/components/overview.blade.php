{{-- resources/views/components/overview.blade.php --}}
@include('components.header')

<div class="flex">
  @include('components.sidebar')

  <main class="flex-1 p-6">
    <div id="overview" data-tab-content>
      <div class="space-y-6">
        <div class="flex items-center justify-between">
          <h2 class="text-2xl font-semibold text-gray-800">Vue d'ensemble</h2>
          <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full border border-green-200 bg-green-50 text-green-700 text-sm">
              <span class="w-2 h-2 bg-green-500 rounded-full inline-block mr-2"></span>
              Système en ligne
            </span>
            <button class="px-3 py-1 rounded-md border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
              <i class="fas fa-download mr-2"></i>
              Rapport
            </button>
          </div>
        </div>

        <!-- Stats Cards -->
        <div id="stats-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Les cartes de stats seront générées dynamiquement -->
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-800">Évolution des revenus</h3>
              <p class="text-sm text-gray-600">Revenus mensuels et nombre de commandes</p>
            </div>
            <div class="p-6">
              <div class="chart-container"></div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-800">Répartition des services</h3>
              <p class="text-sm text-gray-600">Distribution des types de réservations</p>
            </div>
            <div class="p-6">
              <div class="pie-chart"></div>
              <div class="grid grid-cols-2 gap-2 mt-4">
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded bg-yellow-500"></div>
                  <span class="text-xs text-gray-600">Consigne 24h</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded bg-yellow-400"></div>
                  <span class="text-xs text-gray-600">Consigne 48h</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded bg-yellow-300"></div>
                  <span class="text-xs text-gray-600">Transfert Aéroport</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded bg-yellow-200"></div>
                  <span class="text-xs text-gray-600">Autres</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-800">Activité récente</h3>
              <p class="text-sm text-gray-600">Dernières commandes et réservations</p>
            </div>
            <button class="px-3 py-1 rounded-md border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
              Voir tout
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <th class="px-4 py-3">ID</th>
                  <th class="px-4 py-3">Client</th>
                  <th class="px-4 py-3">Service</th>
                  <th class="px-4 py-3">Localisation</th>
                  <th class="px-4 py-3">Prix</th>
                  <th class="px-4 py-3">Statut</th>
                  <th class="px-4 py-3">Heure</th>
                </tr>
              </thead>
              <tbody id="recent-orders" class="divide-y divide-gray-200 text-sm">
                <!-- Les commandes récentes seront générées dynamiquement -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    initOverview();
  });
</script>
