<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Détails Commande - HelloPassenger</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-yellow-400 shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('agent.dashboard') }}" class="text-gray-800 hover:text-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i>Retour
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Détails Commande</h1>
            </div>
            <span class="text-gray-700">Agent: {{ session('agent_email') }}</span>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Commande Info -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Informations Commande</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Référence:</p>
                    <p class="font-bold">{{ $commande->getFormattedReference() }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Client:</p>
                    <p class="font-bold">{{ $commande->client_prenom }} {{ $commande->client_nom }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Email:</p>
                    <p class="font-bold">{{ $commande->client_email }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Téléphone:</p>
                    <p class="font-bold">{{ $commande->client_telephone ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Total:</p>
                    <p class="font-bold">{{ number_format($commande->total_prix_ttc, 2) }} €</p>
                </div>
                <div>
                    <p class="text-gray-600">Statut:</p>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $commande->statut == 'completed' ? 'bg-green-100 text-green-800' : ($commande->statut == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $commande->statut }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Upload Photos Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Ajouter une Photo</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Dépôt -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <h3 class="font-bold mb-4 text-lg">Photo Dépôt</h3>
                    <form id="uploadFormDepot" enctype="multipart/form-data" class="space-y-4">
                        <!-- Camera Preview -->
                        <div id="cameraPreviewDepot" class="hidden mb-4">
                            <video id="videoDepot" autoplay playsinline class="w-full max-w-md mx-auto rounded border-2 border-gray-300"></video>
                            <canvas id="canvasDepot" class="hidden"></canvas>
                            <div class="mt-4 flex gap-2 justify-center">
                                <button type="button" 
                                        onclick="capturePhoto('depot')" 
                                        class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600">
                                    <i class="fas fa-camera mr-2"></i>Capturer
                                </button>
                                <button type="button" 
                                        onclick="stopCamera('depot')" 
                                        class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </button>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2 justify-center">
                            <button type="button" 
                                    onclick="startCamera('depot')" 
                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                <i class="fas fa-video mr-2"></i>Caméra Live
                            </button>
                        </div>
                        
                        <!-- Preview -->
                        <div id="previewDepot" class="mt-4 hidden">
                            <img id="previewImgDepot" src="" alt="Preview" class="max-w-full h-48 mx-auto rounded">
                        </div>
                        
                        <textarea id="notesDepot" 
                                  name="notes" 
                                  placeholder="Notes (optionnel)" 
                                  class="w-full mt-4 p-2 border rounded hidden"></textarea>
                        <button type="submit" 
                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 hidden w-full" 
                                id="submitDepot">
                            <i class="fas fa-upload mr-2"></i>Uploader
                        </button>
                    </form>
                </div>

                <!-- Restitution -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <h3 class="font-bold mb-4 text-lg">Photo Restitution</h3>
                    <form id="uploadFormRestitution" enctype="multipart/form-data" class="space-y-4">
                        <!-- Camera Preview -->
                        <div id="cameraPreviewRestitution" class="hidden mb-4">
                            <video id="videoRestitution" autoplay playsinline class="w-full max-w-md mx-auto rounded border-2 border-gray-300"></video>
                            <canvas id="canvasRestitution" class="hidden"></canvas>
                            <div class="mt-4 flex gap-2 justify-center">
                                <button type="button" 
                                        onclick="capturePhoto('restitution')" 
                                        class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600">
                                    <i class="fas fa-camera mr-2"></i>Capturer
                                </button>
                                <button type="button" 
                                        onclick="stopCamera('restitution')" 
                                        class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </button>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2 justify-center">
                            <button type="button" 
                                    onclick="startCamera('restitution')" 
                                    class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                                <i class="fas fa-video mr-2"></i>Caméra Live
                            </button>
                        </div>
                        
                        <!-- Preview -->
                        <div id="previewRestitution" class="mt-4 hidden">
                            <img id="previewImgRestitution" src="" alt="Preview" class="max-w-full h-48 mx-auto rounded">
                        </div>
                        
                        <textarea id="notesRestitution" 
                                  name="notes" 
                                  placeholder="Notes (optionnel)" 
                                  class="w-full mt-4 p-2 border rounded hidden"></textarea>
                        <button type="submit" 
                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 hidden w-full" 
                                id="submitRestitution">
                            <i class="fas fa-upload mr-2"></i>Uploader
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Photos Gallery -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Photos</h2>
            <div id="photosContainer" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($commande->photos as $photo)
                    <div class="border rounded-lg p-4">
                        <img src="{{ $photo->photo_url }}" 
                             alt="Photo {{ $photo->type }}" 
                             class="w-full h-48 object-cover rounded mb-2">
                        <div class="text-sm">
                            <p class="font-bold">
                                {{ $photo->type == 'depot' ? 'Dépôt' : 'Restitution' }}
                            </p>
                            <p class="text-gray-600">
                                {{ $photo->created_at->format('d/m/Y H:i') }}
                            </p>
                            @if($photo->agent)
                                <p class="text-gray-600">Par: {{ $photo->agent->email }}</p>
                            @endif
                            @if($photo->notes)
                                <p class="text-gray-600 mt-2">{{ $photo->notes }}</p>
                            @endif
                            <button onclick="deletePhoto({{ $photo->id }})" 
                                    class="mt-2 text-red-600 hover:text-red-800">
                                <i class="fas fa-trash mr-1"></i>Supprimer
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($commande->photos->isEmpty())
                <p class="text-gray-600 text-center py-8">Aucune photo pour cette commande</p>
            @endif
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const commandeId = {{ $commande->id }};
        let currentStream = null;
        let currentType = null;

        // Start camera for live preview
        async function startCamera(type) {
            try {
                // Stop any existing camera
                if (currentStream) {
                    stopCamera(currentType);
                }

                currentType = type;
                const videoId = type === 'depot' ? 'videoDepot' : 'videoRestitution';
                const previewId = type === 'depot' ? 'cameraPreviewDepot' : 'cameraPreviewRestitution';
                const video = document.getElementById(videoId);
                const preview = document.getElementById(previewId);

                // Request camera access
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment', // Use back camera on mobile
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                });

                currentStream = stream;
                video.srcObject = stream;
                preview.classList.remove('hidden');
                
                // Hide camera button when camera is active
                const cameraButton = preview.parentElement.querySelector('button[onclick*="startCamera"]');
                if (cameraButton) {
                    cameraButton.style.display = 'none';
                }

            } catch (error) {
                console.error('Error accessing camera:', error);
                alert('Impossible d\'accéder à la caméra. Vérifiez les permissions ou utilisez "Choisir Fichier".');
            }
        }

        // Stop camera
        function stopCamera(type) {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }

            const videoId = type === 'depot' ? 'videoDepot' : 'videoRestitution';
            const previewId = type === 'depot' ? 'cameraPreviewDepot' : 'cameraPreviewRestitution';
            const video = document.getElementById(videoId);
            const preview = document.getElementById(previewId);

            if (video) {
                video.srcObject = null;
            }
            preview.classList.add('hidden');

            // Show camera button again
            const cameraButton = preview.parentElement.querySelector('button[onclick*="startCamera"]');
            if (cameraButton) {
                cameraButton.style.display = '';
            }
        }

        // Capture photo from camera
        function capturePhoto(type) {
            const videoId = type === 'depot' ? 'videoDepot' : 'videoRestitution';
            const canvasId = type === 'depot' ? 'canvasDepot' : 'canvasRestitution';
            const previewId = type === 'depot' ? 'previewDepot' : 'previewRestitution';
            const previewImgId = type === 'depot' ? 'previewImgDepot' : 'previewImgRestitution';
            const notesId = type === 'depot' ? 'notesDepot' : 'notesRestitution';
            const submitId = type === 'depot' ? 'submitDepot' : 'submitRestitution';

            const video = document.getElementById(videoId);
            const canvas = document.getElementById(canvasId);
            const preview = document.getElementById(previewId);
            const previewImg = document.getElementById(previewImgId);

            // Set canvas dimensions to match video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Draw video frame to canvas
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert canvas to blob and create file
            canvas.toBlob(function(blob) {
                // Create a File object from the blob
                const file = new File([blob], 'photo_' + Date.now() + '.jpg', { type: 'image/jpeg' });

                // Show preview
                previewImg.src = canvas.toDataURL('image/jpeg');
                preview.classList.remove('hidden');
                document.getElementById(notesId).classList.remove('hidden');
                document.getElementById(submitId).classList.remove('hidden');

                // Store the blob for upload
                if (type === 'depot') {
                    window.depotPhotoBlob = blob;
                } else {
                    window.restitutionPhotoBlob = blob;
                }

                // Stop camera after capture
                stopCamera(type);
            }, 'image/jpeg', 0.95);
        }


        document.getElementById('uploadFormDepot').addEventListener('submit', async function(e) {
            e.preventDefault();
            await uploadPhoto('depot');
        });


        document.getElementById('uploadFormRestitution').addEventListener('submit', async function(e) {
            e.preventDefault();
            await uploadPhoto('restitution');
        });

        async function uploadPhoto(type) {
            const formId = type === 'depot' ? 'uploadFormDepot' : 'uploadFormRestitution';
            const form = document.getElementById(formId);
            const formData = new FormData();
            
            const notesInput = type === 'depot' ? document.getElementById('notesDepot') : document.getElementById('notesRestitution');
            
            let photoFile = null;

            // Check if we have a blob from camera capture
            const photoBlob = type === 'depot' ? window.depotPhotoBlob : window.restitutionPhotoBlob;
            if (photoBlob) {
                // Create a File from the blob
                photoFile = new File([photoBlob], 'photo_' + Date.now() + '.jpg', { type: 'image/jpeg' });
            } else {
                alert('Veuillez prendre une photo avec la caméra');
                return;
            }

            formData.append('photo', photoFile);
            formData.append('type', type);
            formData.append('notes', notesInput.value);
            formData.append('_token', csrfToken);

            try {
                const response = await fetch(`/agent/commande/${commandeId}/upload-photo`, {
                    method: 'POST',
                    body: formData,
                });

                const data = await response.json();

                if (data.success) {
                    alert('Photo uploadée avec succès !');
                    location.reload();
                } else {
                    alert('Erreur: ' + (data.error || 'Erreur inconnue'));
                }
            } catch (error) {
                alert('Erreur lors de l\'upload: ' + error.message);
            }
        }

        async function deletePhoto(photoId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')) {
                return;
            }

            try {
                const response = await fetch(`/agent/photo/${photoId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    alert('Photo supprimée avec succès !');
                    location.reload();
                } else {
                    alert('Erreur: ' + (data.error || 'Erreur inconnue'));
                }
            } catch (error) {
                alert('Erreur lors de la suppression: ' + error.message);
            }
        }
    </script>
</body>
</html>
