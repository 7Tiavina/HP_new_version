@php
    $formUrl = route('form-consigne') . '?modal=1';
@endphp
{{-- Floating Book now widget — visible partout --}}
<button id="hp-book-widget-btn" class="hp-book-widget-btn" aria-label="Réserver maintenant" title="Réserver maintenant">
    <span class="hp-book-widget-text">Book now</span>
    <svg class="hp-book-widget-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>
</button>

{{-- Full-screen booking modal --}}
<div id="hp-booking-modal" class="hp-booking-modal" aria-hidden="true" role="dialog" aria-labelledby="hp-booking-modal-title">
    <div class="hp-booking-modal-backdrop" id="hp-booking-modal-backdrop"></div>
    <div class="hp-booking-modal-container">
        <div class="hp-booking-modal-header">
            <h2 id="hp-booking-modal-title" class="hp-booking-modal-title">Réserver une consigne</h2>
            <button type="button" class="hp-booking-modal-close" id="hp-booking-modal-close" aria-label="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="hp-booking-modal-body">
            <iframe id="hp-booking-iframe" src="" title="Formulaire de réservation" loading="lazy"></iframe>
        </div>
    </div>
</div>

<style>
@keyframes hp-widget-pulse {
    0%, 100% { box-shadow: 0 4px 20px rgba(0,0,0,0.35), 0 0 0 0 rgba(201, 169, 98, 0.5); }
    50% { box-shadow: 0 4px 24px rgba(201, 169, 98, 0.4), 0 0 0 8px rgba(201, 169, 98, 0); }
}
@keyframes hp-widget-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}
.hp-book-widget-btn {
    position: fixed !important;
    bottom: max(1rem, env(safe-area-inset-bottom, 1rem));
    left: max(1rem, env(safe-area-inset-left, 1rem));
    z-index: 99998 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    min-height: 48px;
    flex-shrink: 0;
    white-space: nowrap;
    background: var(--luxe-gold);
    color: #0f0f0f;
    border: 2px solid rgba(201, 169, 98, 0.5);
    border-radius: var(--luxe-radius, 6px);
    font-family: var(--luxe-font-sans, inherit);
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(0,0,0,0.35);
    transition: transform 0.2s, background 0.2s, box-shadow 0.2s;
    animation: hp-widget-pulse 2.5s ease-in-out infinite;
}
.hp-book-widget-btn:hover {
    background: var(--luxe-gold-light);
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 28px rgba(201, 169, 98, 0.5);
    animation: none;
}
.hp-book-widget-icon { width: 22px; height: 22px; min-width: 22px; min-height: 22px; flex-shrink: 0; }

.hp-booking-modal {
    position: fixed;
    inset: 0;
    z-index: 10060;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.hp-booking-modal[aria-hidden="false"] { display: flex; }
.hp-booking-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.9);
    cursor: pointer;
}
.hp-booking-modal-container {
    position: relative;
    width: 100%;
    max-width: 900px;
    height: 90vh;
    max-height: 900px;
    background: var(--luxe-card);
    border: 1px solid var(--luxe-border);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 24px 48px rgba(0,0,0,0.5);
}
.hp-booking-modal-header {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--luxe-border);
}
.hp-booking-modal-title {
    font-family: var(--luxe-font-serif);
    font-size: 1.35rem;
    font-weight: 600;
    color: var(--luxe-cream);
    margin: 0;
}
.hp-booking-modal-close {
    background: none;
    border: none;
    color: var(--luxe-cream-muted);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: color 0.2s, background 0.2s;
}
.hp-booking-modal-close:hover {
    color: var(--luxe-cream);
    background: rgba(201, 169, 98, 0.15);
}
.hp-booking-modal-body {
    flex: 1;
    min-height: 0;
}
.hp-booking-modal-body iframe {
    width: 100%;
    height: 100%;
    border: none;
    display: block;
}
@media (max-width: 768px) {
    .hp-booking-modal-container {
        width: 100% !important;
        max-width: 100% !important;
        height: 100dvh !important;
        height: 100vh !important;
        max-height: none;
        border-radius: 0;
        margin: 0;
    }
    .hp-booking-modal {
        padding: 0;
        align-items: stretch;
        justify-content: stretch;
    }
    .hp-booking-modal-header {
        padding: 0.75rem 1rem;
        flex-shrink: 0;
    }
    .hp-booking-modal-title { font-size: 1.1rem; }
    .hp-book-widget-btn {
        padding: 0.65rem 1rem;
        font-size: 0.9rem;
        min-height: 44px;
    }
}
@media (max-width: 640px) {
    .hp-book-widget-btn {
        padding: 0.6rem 0.9rem;
        font-size: 0.85rem;
    }
}
@media (max-width: 480px) {
    .hp-book-widget-btn {
        padding: 0.55rem 0.85rem;
        font-size: 0.8rem;
    }
    .hp-book-widget-text { display: none; }
    .hp-book-widget-icon { width: 24px; height: 24px; min-width: 24px; min-height: 24px; }
}
</style>

<script>
(function() {
    var btn = document.getElementById('hp-book-widget-btn');
    var modal = document.getElementById('hp-booking-modal');
    var iframe = document.getElementById('hp-booking-iframe');
    var closeBtn = document.getElementById('hp-booking-modal-close');
    var backdrop = document.getElementById('hp-booking-modal-backdrop');
    var formUrl = @json($formUrl);

    function openModal() {
        modal.setAttribute('aria-hidden', 'false');
        iframe.src = formUrl;
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        modal.setAttribute('aria-hidden', 'true');
        iframe.src = 'about:blank';
        document.body.style.overflow = '';
    }

    if (btn) btn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);
    window.addEventListener('message', function(e) {
        if (e.data === 'hp-booking-close') closeModal();
        if (e.data && e.data.type === 'hp-booking-redirect' && e.data.url) {
            closeModal();
            window.location.href = e.data.url;
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') closeModal();
    });

    window.hpCloseBookingModal = closeModal;
    window.hpOpenBookingModal = openModal;
})();
</script>
