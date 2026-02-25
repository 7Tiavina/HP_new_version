{{-- resources/views/components/reservation_show.blade.php --}}
@include('components.header')

<div class="flex">
  @include('components.sidebar')

  <main class="flex-1 p-6 space-y-8">

    {{-- Détails réservation --}}
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
      <h2 class="text-2xl font-semibold">Détails de la réservation</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p><strong>Référence :</strong> {{ $reservation->ref }}</p>
          <p><strong>Client :</strong> {{ $reservation->user->email }}</p>
          <p><strong>Départ :</strong> {{ $reservation->departure }}</p>
          <p><strong>Arrivée :</strong> {{ $reservation->arrival }}</p>
        </div>
        <div>
          <p><strong>Date de dépôt :</strong> {{ $reservation->collect_date }}</p>
          <p><strong>Date de retrait :</strong> {{ $reservation->deliver_date }}</p>
          <p><strong>Statut :</strong> {{ ucfirst($reservation->status) }}</p>
          <p><strong>Créée le :</strong> {{ $reservation->created_at->format('d/m/Y H:i') }}</p>
        </div>
      </div>
      <div class="pt-4">
        <a href="{{ route('orders') }}"
           class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
          ← Retour aux commandes
        </a>
      </div>
    </div>

    {{-- Section collecte + photo --}}
    <div class="bg-white rounded-lg shadow p-6 space-y-6">
      <h3 class="text-xl font-semibold text-gray-800">Collecte du bagage</h3>

      <div class="flex flex-col md:flex-row items-start gap-6">
        {{-- Zone caméra / preview --}}
        <div class="relative w-full md:w-1/2 aspect-video bg-gray-100 border-2 border-dashed border-yellow-400 rounded-lg flex items-center justify-center">
          <div id="placeholder" class="text-gray-500 text-center">
            <i class="fas fa-video-slash text-4xl mb-2"></i>
            <p>Caméra inactive</p>
          </div>
          <video id="video"
                 class="hidden rounded-lg w-full h-full object-cover"
                 autoplay muted playsinline>
          </video>
          <canvas id="canvas"
                  class="hidden absolute inset-0 w-full h-full object-cover rounded-lg">
          </canvas>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col gap-4 w-full md:w-1/2">
          <button id="openCamBtn"
                  class="w-full px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center justify-center gap-2">
            <i class="fas fa-video"></i> Ouvrir la caméra
          </button>

          <button id="captureBtn"
                  class="hidden w-full px-4 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 flex items-center justify-center gap-2">
            <i class="fas fa-camera"></i> Prendre une photo
          </button>

          <button id="retakeBtn"
                  class="hidden w-full px-4 py-3 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 flex items-center justify-center gap-2">
            <i class="fas fa-redo"></i> Reprendre la photo
          </button>

          <form id="collectForm"
                method="POST"
                action="{{ route('collecter.bagage', $reservation->id) }}">
            @csrf
            <input type="hidden" name="image_data" id="image_data">
            <button type="submit"
                    class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-2">
              <i class="fas fa-check-circle"></i> Confirmer la collecte
            </button>
          </form>
        </div>
      </div>
    </div>

  </main>
</div>

{{-- Script Caméra --}}
<script>
  const video       = document.getElementById('video');
  const canvas      = document.getElementById('canvas');
  const placeholder = document.getElementById('placeholder');
  const openCamBtn  = document.getElementById('openCamBtn');
  const captureBtn  = document.getElementById('captureBtn');
  const retakeBtn   = document.getElementById('retakeBtn');
  const imageInput  = document.getElementById('image_data');
  let   stream;

  function startCamera() {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
      .then(s => {
        stream = s;
        video.srcObject = stream;
        placeholder.classList.add('hidden');
        video.classList.remove('hidden');
        captureBtn.classList.remove('hidden');
        openCamBtn.classList.add('hidden');
      })
      .catch(() => alert("Impossible d’accéder à la caméra."));
  }

  openCamBtn.addEventListener('click', startCamera);

  captureBtn.addEventListener('click', () => {
    const ctx = canvas.getContext('2d');
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/png');
    imageInput.value = dataUrl;

    // afficher la photo, masquer vidéo
    canvas.classList.remove('hidden');
    video.classList.add('hidden');
    captureBtn.classList.add('hidden');
    retakeBtn.classList.remove('hidden');

    // stopper le flux
    stream.getTracks().forEach(track => track.stop());
  });

  retakeBtn.addEventListener('click', () => {
    // réinitialisation UI
    canvas.classList.add('hidden');
    retakeBtn.classList.add('hidden');
    startCamera();
  });
</script>
