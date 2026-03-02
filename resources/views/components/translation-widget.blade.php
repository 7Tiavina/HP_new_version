{{-- Creative translation widget — luxe theme — replaces modal buttons --}}
<div id="hp-lang-widget" class="hp-lang-widget" aria-label="Choisir la langue / Choose language">
    <button type="button" id="hp-lang-trigger" class="hp-lang-trigger" aria-expanded="false" aria-controls="hp-lang-dropdown">
        <img src="https://flagcdn.com/w40/fr.png" alt="FR" class="hp-lang-current-flag" id="hp-lang-current-flag">
        <svg class="hp-lang-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div id="hp-lang-dropdown" class="hp-lang-dropdown" role="menu" aria-hidden="true">
        <button type="button" class="hp-lang-option" data-lang="fr" role="menuitem">
            <img src="https://flagcdn.com/w40/fr.png" alt="FR" class="hp-lang-flag"> Français
        </button>
        <button type="button" class="hp-lang-option" data-lang="en" role="menuitem">
            <img src="https://flagcdn.com/w40/gb.png" alt="EN" class="hp-lang-flag"> English
        </button>
    </div>
</div>

<style>
.hp-lang-widget {
    position: relative;
    display: inline-flex;
}
.hp-lang-trigger {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.7rem;
    background: rgba(201, 169, 98, 0.15);
    border: 1px solid var(--luxe-border);
    border-radius: var(--luxe-radius, 6px);
    color: var(--luxe-cream);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.hp-lang-trigger:hover {
    background: rgba(201, 169, 98, 0.25);
    border-color: var(--luxe-gold);
    color: var(--luxe-gold);
}
.hp-lang-current-flag {
    width: 22px;
    height: 16px;
    border-radius: 2px;
    object-fit: cover;
    transition: opacity 0.15s ease;
    display: block;
}
.hp-lang-chevron { width: 14px; height: 14px; transition: transform 0.2s; }
.hp-lang-trigger[aria-expanded="true"] .hp-lang-chevron { transform: rotate(180deg); }
.hp-lang-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    right: 0;
    min-width: 120px;
    background: var(--luxe-card);
    border: 1px solid var(--luxe-border);
    border-radius: var(--luxe-radius);
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-6px);
    transition: all 0.2s;
    z-index: 100;
}
.hp-lang-widget.is-open .hp-lang-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
.hp-lang-option {
    display: block;
    width: 100%;
    padding: 0.6rem 1rem;
    text-align: left;
    background: none;
    border: none;
    color: var(--luxe-cream-muted);
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.hp-lang-flag {
    width: 20px;
    height: 15px;
    border-radius: 2px;
    object-fit: cover;
}
.hp-lang-option:first-child { border-radius: var(--luxe-radius) var(--luxe-radius) 0 0; }
.hp-lang-option:last-child { border-radius: 0 0 var(--luxe-radius) var(--luxe-radius); }
.hp-lang-option:hover {
    background: rgba(201, 169, 98, 0.15);
    color: var(--luxe-cream);
}
.hp-lang-option.active {
    background: rgba(201, 169, 98, 0.2);
    color: var(--luxe-gold);
    font-weight: 600;
}
</style>

<script>
(function() {
    var widget = document.getElementById('hp-lang-widget');
    var trigger = document.getElementById('hp-lang-trigger');
    var dropdown = document.getElementById('hp-lang-dropdown');
    var currentFlag = document.getElementById('hp-lang-current-flag');
    
    console.log('Translation widget loaded:', {
        widget: !!widget,
        trigger: !!trigger,
        dropdown: !!dropdown,
        currentFlag: !!currentFlag
    });
    
    if (!widget || !trigger || !dropdown || !currentFlag) {
        console.error('Translation widget: Missing elements!');
        return;
    }

    function getLang() {
        return localStorage.getItem('app_language') || 'fr';
    }
    
    function applyLang(lang) {
        console.log('=== [WIDGET] applyLang called with:', lang);
        localStorage.setItem('app_language', lang);
        
        // Call the main translation function FIRST
        if (window.applyLanguage) {
            window.applyLanguage(lang);
        }
        
        // Then update the flag AFTER translations are done
        setTimeout(function() {
            if (lang === 'fr') {
                currentFlag.src = 'https://flagcdn.com/w40/fr.png';
            } else {
                currentFlag.src = 'https://flagcdn.com/w40/gb.png';
            }
            currentFlag.alt = lang.toUpperCase();
            console.log('[WIDGET] Flag updated to:', lang, currentFlag.src);
        }, 100);
        
        dropdown.querySelectorAll('.hp-lang-option').forEach(function(btn) {
            btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
        });
        
        closeDropdown();
    }
    function closeDropdown() {
        widget.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        dropdown.setAttribute('aria-hidden', 'true');
    }
    function openDropdown() {
        widget.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
        dropdown.setAttribute('aria-hidden', 'false');
    }
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        if (widget.classList.contains('is-open')) closeDropdown();
        else openDropdown();
    });
    dropdown.querySelectorAll('.hp-lang-option').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            applyLang(btn.getAttribute('data-lang'));
        });
    });
    document.addEventListener('click', function() { closeDropdown(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDropdown();
    });

    // Initialize with current language
    var initialLang = getLang();
    var initialFlagUrl = initialLang === 'fr' ? 'https://flagcdn.com/w40/fr.png' : 'https://flagcdn.com/w40/gb.png';
    currentFlag.src = initialFlagUrl;
    currentFlag.alt = initialLang.toUpperCase();
    dropdown.querySelector('.hp-lang-option[data-lang="' + initialLang + '"]')?.classList.add('active');
})();
</script>
