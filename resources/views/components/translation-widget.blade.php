@php
    $currentLang = session('app_language', 'fr');
    $otherLang = $currentLang === 'fr' ? 'en' : 'fr';
    $flagUrl = $currentLang === 'fr' 
        ? 'https://flagcdn.com/w40/fr.png' 
        : 'https://flagcdn.com/w40/gb.png';
    $otherFlagUrl = $otherLang === 'fr' 
        ? 'https://flagcdn.com/w40/fr.png' 
        : 'https://flagcdn.com/w40/gb.png';
@endphp

<a href="{{ route('set-language', ['lang' => $otherLang]) }}" 
   class="hp-lang-toggle" 
   title="Switch to {{ strtoupper($otherLang) }}">
    <img src="{{ $otherFlagUrl }}" alt="{{ strtoupper($otherLang) }}" class="hp-lang-flag">
    <span class="hp-lang-code">{{ strtoupper($otherLang) }}</span>
</a>

<style>
.hp-lang-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 6px;
    text-decoration: none;
    color: #1a1a1a;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.hp-lang-toggle:hover {
    background: transparent;
    border-color: transparent;
    color: #FAC12E;
}

.hp-lang-toggle:hover .hp-lang-code {
    color: #FAC12E;
}

.hp-lang-flag {
    width: 20px;
    height: 15px;
    border-radius: 2px;
    object-fit: cover;
}

.hp-lang-code {
    font-size: 13px;
    font-weight: 700;
}
</style>
