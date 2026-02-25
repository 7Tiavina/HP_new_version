<aside class="w-64 bg-white border-r sidebar">
  <nav class="p-6">
    <div class="space-y-2">
      <a href="{{ route('dashboard') }}"
         class="w-full flex items-center px-4 py-2 rounded-md {{ request()->routeIs('dashboard') ? 'bg-yellow-400 text-gray-800 hover:bg-yellow-500' : 'hover:bg-gray-100 text-gray-700' }}">
        <i class="fas fa-home mr-3"></i>
        Dashboard
      </a>

      <a href="{{ route('overview') }}"
         class="w-full flex items-center px-4 py-2 rounded-md {{ request()->routeIs('overview') ? 'bg-yellow-400 text-gray-800 hover:bg-yellow-500' : 'hover:bg-gray-100 text-gray-700' }}">
        <i class="fas fa-chart-line mr-3"></i>
        Vue d'ensemble
      </a>

      <div class="relative">
        <button onclick="toggleDropdown(this)"
          class="w-full flex items-center px-4 py-2 rounded-md bg-yellow-400 text-gray-800 hover:bg-yellow-500 transition">
          <i class="fas fa-box mr-3"></i>
          Commandes
          <svg class="ml-auto w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M5.5 7l4.5 4.5L14.5 7z" />
          </svg>
        </button>
        <div class="hidden mt-1 ml-6 space-y-1 dropdown-menu">
          <a href="{{ route('orders') }}" class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-100">
            Nouvelle commande
          </a>
          <a href="{{ route('myorders') }}" class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-100">
            Mes commandes
          </a>
        </div>
      </div>

      <a href="{{ route('users') }}"
         class="w-full flex items-center px-4 py-2 rounded-md {{ request()->routeIs('users') ? 'bg-yellow-400 text-gray-800 hover:bg-yellow-500' : 'hover:bg-gray-100 text-gray-700' }}">
        <i class="fas fa-users mr-3"></i>
        Utilisateurs
      </a>

      <a href="{{ route('chat') }}"
         class="w-full flex items-center px-4 py-2 rounded-md {{ request()->routeIs('chat') ? 'bg-yellow-400 text-gray-800 hover:bg-yellow-500' : 'hover:bg-gray-100 text-gray-700' }}">
        <i class="fas fa-comments mr-3"></i>
        Chat
      </a>


      <a href="{{ route('analytics') }}"
         class="w-full flex items-center px-4 py-2 rounded-md {{ request()->routeIs('analytics') ? 'bg-yellow-400 text-gray-800 hover:bg-yellow-500' : 'hover:bg-gray-100 text-gray-700' }}">
        <i class="fas fa-chart-bar mr-3"></i>
        Analytiques
      </a>
    </div>

    <!-- Live Notifications -->
    <div class="mt-8">
      <p class="text-xs text-gray-500 mb-3 uppercase tracking-wider">Notifications</p>
      <div class="space-y-3">
        <div class="text-xs p-2 bg-gray-50 rounded-lg">
          <p class="text-gray-800 mb-1">Nouvelle réservation de Marie Dubois</p>
          <p class="text-gray-500">Il y a 5 min</p>
        </div>
        <div class="text-xs p-2 bg-gray-50 rounded-lg">
          <p class="text-gray-800 mb-1">Consigne #47 bientôt libérée</p>
          <p class="text-gray-500">Il y a 15 min</p>
        </div>
        <div class="text-xs p-2 bg-gray-50 rounded-lg">
          <p class="text-gray-800 mb-1">Paiement reçu - €45</p>
          <p class="text-gray-500">Il y a 30 min</p>
        </div>
      </div>
    </div>
  </nav>
</aside>
<script>
  function toggleDropdown(button) {
    const menu = button.nextElementSibling;
    menu.classList.toggle('hidden');
  }
</script>