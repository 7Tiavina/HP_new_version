{{-- resources/views/components/myorders.blade.php --}}
@include('components.header')

<style>[x-cloak] { display: none !important; }</style>

<div class="flex">
  @include('components.sidebar')

  <main class="flex-1 p-6" x-data="{
    openModal: false,
    selectedHistory: null,
    async downloadQRPDF(reservation) {
        // Charger jsPDF dynamiquement
        if (!window.jspdf) {
            await new Promise((resolve) => {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
                script.onload = resolve;
                document.head.appendChild(script);
            });
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Ajouter le titre HelloPassenger
        doc.setFontSize(24);
        doc.text('HelloPassenger', 105, 20, null, null, 'center');
        
        // Ajouter la référence
        doc.setFontSize(18);
        doc.text(`Référence: ${reservation.ref}`, 105, 35, null, null, 'center');
        
        // Convertir SVG en image
        const qrImg = new Image();
        qrImg.crossOrigin = 'Anonymous';
        qrImg.src = reservation.qr_svg;
        
        await new Promise((resolve) => {
            qrImg.onload = resolve;
        });
        
        // Créer un canvas pour la conversion
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = qrImg.width;
        canvas.height = qrImg.height;
        ctx.drawImage(qrImg, 0, 0);
        
        const pngDataUrl = canvas.toDataURL('image/png');
        
        // Ajouter le QR code
        const imgWidth = 80;
        const imgHeight = 80;
        const pageWidth = doc.internal.pageSize.getWidth();
        const x = (pageWidth - imgWidth) / 2;
        doc.addImage(pngDataUrl, 'PNG', x, 45, imgWidth, imgHeight);
        
        // Ajouter le message
        doc.setFontSize(14);
        doc.text('Merci d\'utiliser nos services !', 105, 140, null, null, 'center');
        
        // Sauvegarder
        doc.save(`bagage-${reservation.ref}.pdf`);
    }
}" x-cloak>
    <h2 class="text-2xl font-bold mb-4">Mes collectes de bagages</h2>

    {{-- Tableau --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full text-sm divide-y divide-gray-200">
        <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold">
          <tr>
            <th class="px-4 py-3 text-left">Référence</th>
            <th class="px-4 py-3 text-left">Client</th>
            <th class="px-4 py-3 text-left">Statut</th>
            <th class="px-4 py-3 text-left">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          @foreach ($reservations as $res)
            <tr>
              <td class="px-4 py-3 font-medium">{{ $res->ref }}</td>
              <td class="px-4 py-3">{{ $res->user->email }}</td>
              <td class="px-4 py-3 capitalize">{{ $res->status }}</td>
              <td class="px-4 py-3">
                <button
                  class="text-blue-600 hover:underline font-semibold"
                  @click.prevent="
                    selectedHistory = {{ $res->toJson() }};
                    openModal = true;
                  "
                >
                  Voir
                </button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Modal --}}
    <div
      x-show="openModal"
      x-transition.opacity
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4"
    >
      <div class="bg-white w-full max-w-2xl p-6 rounded-xl shadow-xl relative overflow-hidden">
        <h3 class="text-2xl font-bold mb-4 text-gray-800">Historique du bagage</h3>

        {{-- Historique --}}
        <template x-if="selectedHistory">
          <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2">
            <template x-for="history in selectedHistory.histories || []" :key="history.id">
              <div class="p-4 rounded-lg border border-gray-200 bg-gray-50 space-y-2">
                <p class="text-sm"><strong>Statut :</strong> <span x-text="history.status"></span></p>
                <p class="text-sm"><strong>Agent :</strong> <span x-text="history.agent?.email ?? 'Inconnu'"></span></p>
                <p class="text-sm"><strong>Date :</strong> <span x-text="new Date(history.timestamp).toLocaleString()"></span></p>

                <template x-if="history.photo_url">
                  <div class="pt-2">
                    <img
                      :src="history.photo_url.startsWith('/') 
                        ? '{{ url('') }}' + history.photo_url 
                        : history.photo_url"
                      class="w-40 rounded shadow-md border"
                      alt="Photo bagage"
                    >
                  </div>
                </template>    
              </div>
            </template>
          </div>
        </template>

        {{-- QR + Infos --}}
        <template x-if="selectedHistory">
          <div class="mt-6 flex flex-col md:flex-row items-center md:items-start gap-6">
            {{-- QR Code --}}
            <div class="flex-shrink-0 text-center mb-4 md:mb-0">
              <p class="font-semibold mb-2">QR Code</p>
              <img 
                :src="selectedHistory.qr_svg" 
                alt="QR Code" 
                class="border p-1 bg-white rounded shadow-sm w-32 h-32"
              >
              <button 
                @click="downloadQRPDF(selectedHistory)"
                class="mt-2 flex items-center justify-center gap-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm w-full"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Télécharger PDF
              </button>
            </div>

            {{-- Infos en deux colonnes --}}
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1">
                <p><strong>Référence :</strong> <span x-text="selectedHistory.ref"></span></p>
                <p><strong>Client :</strong> <span x-text="selectedHistory.user.email"></span></p>
                <p><strong>Départ :</strong> <span x-text="selectedHistory.departure"></span></p>
                <p><strong>Arrivée :</strong> <span x-text="selectedHistory.arrival"></span></p>
              </div>
              <div class="space-y-1">
                <p><strong>Date dépôt :</strong> <span x-text="selectedHistory.collect_date"></span></p>
                <p><strong>Date retrait :</strong> <span x-text="selectedHistory.deliver_date"></span></p>
                <p><strong>Statut :</strong> <span x-text="selectedHistory.status"></span></p>
                <p><strong>Créée le :</strong> <span x-text="new Date(selectedHistory.created_at).toLocaleString()"></span></p>
              </div>
            </div>
          </div>
        </template>

        {{-- Bouton Fermer --}}
        <button
          @click="openModal = false"
          class="mt-6 w-full py-2 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600"
        >
          Fermer
        </button>
      </div>
    </div>

  </main>
</div>

<script src="//unpkg.com/alpinejs" defer></script>