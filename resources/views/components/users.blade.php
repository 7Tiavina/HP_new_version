{{-- resources/views/components/users.blade.php --}}
@include('components.header')

<div class="flex">
  @include('components.sidebar')

  <!-- MODALE CREATION UTILISATEUR -->
  <div id="userModalCreate" class="fixed inset-0 bg-gray-800 bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6 relative">
      <h2 class="text-xl font-semibold mb-4 text-gray-800">Créer un nouvel utilisateur</h2>
      <form method="POST" action="{{ route('users.create') }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-sm text-gray-700">Nom</label>
          <input type="text" name="name" required class="w-full px-3 py-2 border rounded-md focus:ring-yellow-400 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm text-gray-700">Email</label>
          <input type="email" name="email" required class="w-full px-3 py-2 border rounded-md focus:ring-yellow-400 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm text-gray-700">Rôle</label>
          <select name="role" required class="w-full px-3 py-2 border rounded-md focus:ring-yellow-400 focus:outline-none">
            <option value="user">Utilisateur</option>
            <option value="agent">Agent</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-700">Mot de passe</label>
          <input type="password" name="password" required class="w-full px-3 py-2 border rounded-md focus:ring-yellow-400 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm text-gray-700">Confirmer mot de passe</label>
          <input type="password" name="password_confirmation" required class="w-full px-3 py-2 border rounded-md focus:ring-yellow-400 focus:outline-none">
        </div>
        <div class="flex justify-end gap-3 mt-4">
          <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-sm text-gray-600 hover:underline">Annuler</button>
          <button type="submit" class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 rounded-md">Créer</button>
        </div>
      </form>
      <button onclick="closeCreateModal()" class="absolute top-2 right-3 text-gray-400 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>

  <!-- MODALE INFO UTILISATEUR -->
  <div id="userModalInfo" class="fixed inset-0 bg-black bg-opacity-30 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
      <button onclick="closeInfoModal()" class="absolute top-3 right-3 text-gray-600 hover:text-black">
        <i class="fas fa-times"></i>
      </button>
      <div class="flex flex-col items-center text-center">
        <img id="modalPhoto" src="" class="w-24 h-24 rounded-full object-cover mb-4" alt="Photo de profil">
        <h3 id="modalName" class="text-lg font-bold text-gray-800"></h3>
        <p id="modalEmail" class="text-sm text-gray-600 mb-2"></p>
        <p id="modalRole" class="text-sm text-yellow-700 mb-2 font-semibold"></p>
        <p><strong>Mot de passe :</strong> <span id="modalPassword" class="text-red-600"></span></p>
      </div>
    </div>
  </div>

  <main class="flex-1 p-6">
    <div id="users" data-tab-content class="space-y-10">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold text-gray-800">Gestion des utilisateurs</h2>
        <div class="flex items-center gap-3">
          <button
            onclick="exportUsers()"
            class="px-3 py-1 rounded-md border border-gray-300 text-gray-700 text-sm hover:bg-gray-50 flex items-center"
          >
            <i class="fas fa-download mr-2"></i>Exporter
          </button>
          <button
            onclick="openCreateModal()"
            class="px-4 py-2 rounded-md bg-yellow-400 text-gray-800 hover:bg-yellow-500 flex items-center"
          >
            <i class="fas fa-plus mr-2"></i>Nouvel utilisateur
          </button>
        </div>
      </div>

      {{-- Tableau des Agents --}}
      <div>
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Liste des agents</h3>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Nom</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Email</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Rôle</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($agents as $agent)
              <tr>
                <td class="px-4 py-2 text-gray-800">{{ $agent->name }}</td>
                <td class="px-4 py-2">{{ $agent->email }}</td>
                <td class="px-4 py-2">Agent</td>
                <td class="px-4 py-2">
                  <button onclick="openInfoModal({{ $agent->id }})"
                    class="bg-[#fbbf24] text-white px-3 py-1 rounded hover:bg-yellow-400 transition">
                    Infos
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      {{-- Tableau des Utilisateurs --}}
      <div>
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Liste des utilisateurs</h3>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Nom</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Email</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Rôle</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($users as $user)
              <tr>
                <td class="px-4 py-2 text-gray-800">{{ $user->name }}</td>
                <td class="px-4 py-2">{{ $user->email }}</td>
                <td class="px-4 py-2">Utilisateur</td>
                <td class="px-4 py-2">
                  <button onclick="openInfoModal({{ $user->id }})"
                    class="bg-[#fbbf24] text-white px-3 py-1 rounded hover:bg-yellow-400 transition">
                    Infos
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  const users = @json(array_merge($agents->toArray(), $users->toArray()));

  function openInfoModal(id) {
    const user = users.find(u => u.id === id);
    document.getElementById('modalPhoto').src = user.photo ?? 'https://via.placeholder.com/150';
    document.getElementById('modalName').innerText = user.name;
    document.getElementById('modalEmail').innerText = user.email;
    document.getElementById('modalRole').innerText = user.role === 'agent' ? 'Agent' : 'Utilisateur';
    document.getElementById('modalPassword').innerText = user.password_plain ?? 'Non disponible';
    document.getElementById('userModalInfo').classList.remove('hidden');
    document.getElementById('userModalInfo').classList.add('flex');
  }

  function closeInfoModal() {
    document.getElementById('userModalInfo').classList.add('hidden');
    document.getElementById('userModalInfo').classList.remove('flex');
  }

  function openCreateModal() {
    document.getElementById('userModalCreate').classList.remove('hidden');
    document.getElementById('userModalCreate').classList.add('flex');
  }

  function closeCreateModal() {
    document.getElementById('userModalCreate').classList.add('hidden');
    document.getElementById('userModalCreate').classList.remove('flex');
  }
</script>
