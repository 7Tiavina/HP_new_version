<!-- Chatbot Widget - Global Component (CSS chargé dans layout head) -->
<script>
    // Configuration du chatbot - MUST be set before chatbot.js loads
    window.chatbotConfig = {
        whatsappNumber: '{{ config("services.whatsapp.support_number", "+33612345678") }}',
        whatsappMessage: '{{ config("services.whatsapp.support_message", "Bonjour, j\'ai besoin d\'aide avec HelloPassenger") }}'
    };
    console.log('[Chatbot] Config loaded:', window.chatbotConfig);
</script>
<script src="{{ asset('js/chatbot.js') }}"></script>
