@include('components.header')

<div class="flex">
  @include('components.sidebar')

  <main class="flex-1 p-6">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold text-gray-800">Analytiques avancées</h2>
        <div class="flex items-center gap-3">
          <select
            name="range"
            onchange="updateAnalytics(this.value)"
            class="w-48 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-400"
          >
            <option value="30d" selected>30 derniers jours</option>
            <option value="7d">7 derniers jours</option>
            <option value="90d">3 derniers mois</option>
            <option value="1y">Année</option>
          </select>
          <button
            onclick="exportAnalytics()"
            class="flex items-center px-3 py-1 border border-gray-300 rounded-md text-gray-700 text-sm hover:bg-gray-50"
          >
            <i class="fas fa-download mr-2"></i>Exporter
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Évolution des réservations</h3>
          <div id="chart-reservations" class="chart-container"></div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Répartition par service</h3>
          <div id="chart-services" class="pie-chart"></div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  function updateAnalytics(range) {
    console.log('Chargement analytiques pour', range);
  }

  function exportAnalytics() {
    console.log('Export analytiques');
  }

  document.addEventListener('DOMContentLoaded', () => {
    initAnalyticsCharts();
  });

  function initAnalyticsCharts() {
    // TODO: initialiser tes graphiques
  }
</script>
