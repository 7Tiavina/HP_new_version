{{-- resources/views/components/chat.blade.php --}}
@include('components.header')

<div class="flex">
  @include('components.sidebar')

  <main class="flex-1 p-6 flex flex-col">
    <div class="flex-1 bg-white rounded-lg shadow-sm p-4 overflow-y-auto" id="chat-window">
      <!-- Les messages du chat seront injectés ici -->
    </div>

    <form id="chat-form" class="mt-4 flex items-center gap-2">
      <input
        type="text"
        id="chat-input"
        placeholder="Écrire un message..."
        class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-400"
      >
      <button
        type="submit"
        class="px-4 py-2 bg-yellow-400 text-gray-800 rounded-md hover:bg-yellow-500"
      >
        Envoyer
      </button>
    </form>
  </main>
</div>

<script>
  document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const text = input.value.trim();
    if (!text) return;

    // Affiche le message utilisateur
    const bubble = document.createElement('div');
    bubble.className = 'mb-2 p-2 bg-yellow-100 rounded-md self-end max-w-xs';
    bubble.textContent = text;
    document.getElementById('chat-window').appendChild(bubble);

    input.value = '';
    input.focus();

    // TODO: envoyer en AJAX et afficher la réponse bot
  });
</script>
