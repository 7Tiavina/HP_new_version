{{-- resources/views/components/orders.blade.php --}}
@include('components.header')

<div class="flex">
  @include('components.sidebar')

  <main class="flex-1 p-6">
    {{-- Header et bouton Scanner --}}
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-semibold">Gestion des commandes</h2>
      <button
        onclick="openScannerModal()"
        class="px-4 py-2 bg-yellow-400 text-gray-800 rounded hover:bg-yellow-500 flex items-center"
      >
        <i class="fas fa-qrcode mr-2"></i>Scanner
      </button>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow-sm p-4 flex items-center gap-3">
        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
        <div>
          <p class="text-sm text-gray-600">Terminées</p>
          <p class="text-xl text-gray-800">156</p>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow-sm p-4 flex items-center gap-3">
        <i class="fas fa-clock text-orange-600 text-2xl"></i>
        <div>
          <p class="text-sm text-gray-600">En cours</p>
          <p class="text-xl text-gray-800">24</p>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow-sm p-4 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
        <div>
          <p class="text-sm text-gray-600">En attente</p>
          <p class="text-xl text-gray-800">8</p>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow-sm p-4 flex items-center gap-3">
        <i class="fas fa-euro-sign text-blue-600 text-2xl"></i>
        <div>
          <p class="text-sm text-gray-600">Revenus jour</p>
          <p class="text-xl text-gray-800">€450</p>
        </div>
      </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
      <div class="flex flex-wrap items-center gap-4">
        <div class="relative flex-1 min-w-[200px]">
          <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
          <input
            type="text"
            placeholder="Rechercher une commande..."
            class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-400"
            oninput="filterOrders(this.value)"
          >
        </div>
        <select
          class="w-48 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-400"
          onchange="filterByStatus(this.value)"
        >
          <option value="">Filtrer par statut</option>
          <option value="active">Actif</option>
          <option value="completed">Terminé</option>
          <option value="pending">En attente</option>
        </select>
        <select
          class="w-48 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-400"
          onchange="filterByService(this.value)"
        >
          <option value="">Service</option>
          <option value="consigne">Consigne</option>
          <option value="transfert">Transfert</option>
        </select>
        <button
          onclick="applyFilters()"
          class="px-3 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 flex items-center"
        >
          <i class="fas fa-filter mr-2"></i>Filtres
        </button>
      </div>
    </div>

    {{-- Table des commandes --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm divide-y divide-gray-200">
          <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-xs font-medium">
            <tr>
              <th class="px-4 py-3">ID</th>
              <th class="px-4 py-3">Refference</th>
              <th class="px-4 py-3">Client</th>
              <th class="px-4 py-3">Consigne</th>
              <th class="px-4 py-3">Date depart</th>
              <th class="px-4 py-3">_</th>
              <th class="px-4 py-3">Statut</th>
              <th class="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody id="orders-table" class="bg-white divide-y divide-gray-200">
            <!-- Lignes générées dynamiquement -->
            @foreach($reservations as $res)
            <tr>
              <td class="px-4 py-3">{{ $res->id }}</td>
              <td class="px-4 py-3">{{ $res->ref }}</td>
              <td class="px-4 py-3">{{ $res->user->name ?? '-' }}</td>
              <td class="px-4 py-3">Consigne</td>
              <td class="px-4 py-3">{{ $res->departure }} → {{ $res->arrival }}</td>
              <td class="px-4 py-3">{{ \Carbon\Carbon::parse($res->collect_date)->format('d/m/Y') }}</td>
              <td class="px-4 py-3">—</td>
              <td class="px-4 py-3">{{ $res->status }}</td>
              <td class="px-4 py-3">
                <a href="{{ url('/reservations/ref/' . $res->ref) }}" class="text-yellow-600 hover:underline">
                  Voir
                </a>
              </td>
            </tr>
            @endforeach

          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

{{-- Modal Scanner --}}
<div id="scannerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
  <div class="bg-white p-6 rounded-lg shadow-lg relative w-full max-w-sm">
    <button onclick="closeScannerModal()" class="absolute top-3 right-3 text-gray-600 hover:text-black">
      <i class="fas fa-times"></i>
    </button>
    <h3 class="mb-4 text-lg font-semibold">Scannez le QR Code</h3>
    <video id="qrVideo" class="w-full rounded-md"></video>
    <canvas id="qrCanvas" class="hidden"></canvas>
  </div>
</div>

{{-- jsQR et script de scan --}}
<script src="https://unpkg.com/jsqr/dist/jsQR.js"></script>
<script>
  let video, canvas, ctx, scanning = false;

  function openScannerModal(){
    document.getElementById('scannerModal').classList.remove('hidden');
    startScanner();
  }
  function closeScannerModal(){
    document.getElementById('scannerModal').classList.add('hidden');
    stopScanner();
  }

  function startScanner() {
    video  = document.getElementById('qrVideo');
    canvas = document.getElementById('qrCanvas');
    ctx    = canvas.getContext('2d');

    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
      .then(stream => {
        video.srcObject = stream;
        video.play();
        scanning = true;
        scanFrame();
      });
  }

  function stopScanner() {
    scanning = false;
    video.srcObject.getTracks().forEach(t => t.stop());
  }

  function scanFrame() {
  if (!scanning) return;
  if (video.readyState === video.HAVE_ENOUGH_DATA) {
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, canvas.width, canvas.height);
    if (code) {
      stopScanner();
      closeScannerModal();

      // 1) On lit raw = code.data
      let raw = code.data;

      // 2) Tente de parser en JSON et d’en extraire .ref
      try {
        const obj = JSON.parse(raw);
        if (obj.ref) {
          raw = obj.ref; 
        }
      } catch (e) {
        // si JSON.parse échoue, on garde raw tel quel
      }

      // 3) console.log pour debug
      console.log('Redirecting to ref:', raw);

      // 4) Redirection finale
      window.location.href = `/reservations/ref/${encodeURIComponent(raw)}`;
      return;
    }
  }
  requestAnimationFrame(scanFrame);
}

</script>

