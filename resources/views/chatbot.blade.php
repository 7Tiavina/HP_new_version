<!-- Chatbot Widget - Global Component -->
<link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
<script>
    // Configuration du chatbot
    window.chatbotConfig = {
        whatsappNumber: '{{ config("services.whatsapp.support_number", "+33612345678") }}',
        whatsappMessage: '{{ config("services.whatsapp.support_message", "Bonjour, j\'ai besoin d\'aide") }}'
    };
    console.log('[Chatbot] Config loaded:', window.chatbotConfig);
</script>
<script src="{{ asset('js/chatbot.js') }}" defer></script>
