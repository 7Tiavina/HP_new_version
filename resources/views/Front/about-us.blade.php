@extends('layouts.front')

@section('title', 'About Us — Hello Passenger')
@section('meta_description', 'Hello Passenger is operated by Bagages du Monde, official partner of Aéroports de Paris (ADP). Secure luggage at CDG and Orly.')

@section('content')
<div class="luxe-page">
    <section class="luxe-reveal">
        <h1 data-i18n="about_title">Rooted in Paris Airports. Driven by People.</h1>
        <p data-i18n="about_intro">Hello Passenger is operated by <strong class="text-white">Bagages du Monde</strong>, official partner of <strong class="text-white">Aéroports de Paris (Groupe ADP)</strong> since 2003. Over twenty years at <strong class="text-white">Paris-Charles de Gaulle (CDG)</strong> and <strong class="text-white">Paris-Orly (ORY)</strong>.</p>
    </section>

    <section class="luxe-page-window luxe-reveal luxe-reveal-delay-1">
        <img src="{{ asset('images/airport-hero.jpg') }}" alt="Paris Charles de Gaulle Airport" loading="lazy" style="width: 100%; height: auto; display: block; border-radius: var(--luxe-radius, 6px);">
    </section>

    <section class="luxe-page-card luxe-reveal luxe-shine-wrap">
        <h2 data-i18n="about_security">Security You Can See. People You Can Trust.</h2>
        <p data-i18n="about_security_text">Every item: <strong class="text-white">100% X-ray control</strong>; <strong class="text-white">CCTV-monitored, alarm-protected</strong> storage; <strong class="text-white">controlled access</strong>; <strong class="text-white">fully traceable</strong>. Procedures in line with <strong class="text-white">CSI (Code de sécurité intérieure)</strong>.</p>
        @if(file_exists(public_path('rayonx.png')))
            <div class="luxe-xray-block">
                <div class="luxe-xray-frame">
                    <img src="{{ asset('rayonx.png') }}" alt="Contrôle X-ray — 100% des articles contrôlés" loading="lazy">
                </div>
                <p class="luxe-xray-caption" data-i18n="about_xray_caption">100% X-ray control — Every item screened</p>
            </div>
        @endif
    </section>

    <section class="luxe-page-card luxe-reveal luxe-shine-wrap luxe-reveal-delay-1">
        <h2 data-i18n="about_culture">A Culture of Responsibility</h2>
        <p data-i18n="about_culture_text">Uniforms, ID, security clearance, continuous training, attentiveness. Our teams at CDG and ORY are trained, certified, and security-cleared. Trust is built face to face.</p>
    </section>

    <section class="luxe-page-card luxe-reveal luxe-shine-wrap luxe-reveal-delay-2">
        <h2 data-i18n="about_professionals">The Professionals Who Make It Possible</h2>
        <p data-i18n="about_professionals_text">Meet the people behind the service — our teams at CDG and ORY make your luggage handling smooth and secure.</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
            <div class="luxe-page-card luxe-reveal" style="text-align: center; padding: 1.25rem;">
                <div style="height: 120px; border-radius: var(--luxe-radius); background: var(--luxe-surface); display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;"><span style="font-size: 0.85rem; opacity: 0.8;" data-i18n="about_team">Team</span></div>
                <p style="margin: 0; font-size: 0.9rem;" data-i18n="about_team_desc">Security and 20+ years of experience</p>
            </div>
            <div class="luxe-page-card luxe-reveal luxe-reveal-delay-1" style="text-align: center; padding: 1.25rem;">
                <div style="height: 120px; border-radius: var(--luxe-radius); background: var(--luxe-surface); display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;"><span style="font-size: 0.85rem; opacity: 0.8;" data-i18n="about_client_exp">Client experience</span></div>
                <p style="margin: 0; font-size: 0.9rem;" data-i18n="about_support">Dedicated support</p>
            </div>
            <div class="luxe-page-card luxe-reveal luxe-reveal-delay-2" style="text-align: center; padding: 1.25rem;">
                <div style="height: 120px; border-radius: var(--luxe-radius); background: var(--luxe-surface); display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;"><span style="font-size: 0.85rem; opacity: 0.8;" data-i18n="about_monitored">Monitored storage</span></div>
                <p style="margin: 0; font-size: 0.9rem;" data-i18n="about_cctv">CCTV &amp; alarm-protected</p>
            </div>
        </div>
    </section>

    <section class="luxe-page-card luxe-reveal luxe-shine-wrap luxe-reveal-delay-3">
        <h2 data-i18n="about_why">Why Choose Us</h2>
        <ul style="color: var(--luxe-cream-muted); margin: 0; padding-left: 1.25rem;">
            <li data-i18n="about_why_1">100% X-ray control for every item</li>
            <li data-i18n="about_why_2">CCTV-monitored, alarm-protected storage</li>
            <li data-i18n="about_why_3">20+ years at Paris CDG and Orly</li>
            <li data-i18n="about_why_4">Trained, certified, security-cleared teams</li>
            <li data-i18n="about_why_5">Trusted by travelers worldwide</li>
        </ul>
    </section>

    <p style="text-align: center; margin-top: 2rem;" class="luxe-reveal">
        <a href="{{ route('front.acceuil') }}" data-i18n="back_home">← Back to home</a>
    </p>
</div>
@endsection
