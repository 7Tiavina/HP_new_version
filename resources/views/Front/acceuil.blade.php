@extends('layouts.front')

@section('title', 'Hello Passenger — Easy airport travel solutions at Paris CDG & Orly')
@section('meta_description', 'Travel light, luggage free. Book luggage transport or left luggage at Paris CDG and Orly. Hello Passenger — trusted since 2001.')

@section('content')
@php
    $formUrl = route('form-consigne');
    $aboutUrl = route('about-us');
    $faqUrl = route('faq');
@endphp

    {{-- Hero with background image --}}
    <section class="luxe-hero has-bg">
        <div class="luxe-hero-bg">
            <img src="{{ asset('images/airport-hero.jpg') }}" alt="Airport travel" loading="eager">
        </div>
        <div class="luxe-hero-inner luxe-reveal">
            <p class="luxe-hero-tag">PROMOHIVER – 10%</p>
            <p class="luxe-hero-tag luxe-float" style="margin-bottom: 0.5rem;" data-i18n="hero_discover">Discover</p>
            <h1 data-i18n="hero_title">easy airport<br>travel solutions</h1>
            <p class="luxe-hero-desc" data-i18n="hero_desc">Travel Light, Luggage Free with our platform.</p>
            <a href="{{ $formUrl }}" class="btn-cta" data-i18n="nav_book">Book now</a>
        </div>
    </section>

    {{-- Intro --}}
    <section class="luxe-section">
        <div class="luxe-intro luxe-reveal">
            <h2 class="luxe-section-title" data-i18n="intro_title">Traveling has never been easier!</h2>
            <p data-i18n="intro_text">Hello Passenger is a specialist in airport passenger services. Whether you are traveling to or from Paris, visiting or leaving, alone, as a couple, or with children, we make your journey easy and stress-free.</p>
        </div>

        {{-- Stats --}}
        <div class="luxe-stats">
            <div class="luxe-stat luxe-reveal luxe-reveal-delay-1">
                <div class="luxe-stat-value luxe-counter" data-target="98" data-suffix="%">0</div>
                <div class="luxe-stat-label" data-i18n="stat_experience">Experience</div>
            </div>
            <div class="luxe-stat luxe-reveal luxe-reveal-delay-2">
                <div class="luxe-stat-value luxe-counter" data-target="98" data-suffix="%">0</div>
                <div class="luxe-stat-label" data-i18n="stat_success">Success Rate</div>
            </div>
            <div class="luxe-stat luxe-reveal luxe-reveal-delay-3">
                <div class="luxe-stat-value luxe-counter" data-target="2001" data-suffix="">0</div>
                <div class="luxe-stat-label" data-i18n="stat_since">At your service since</div>
            </div>
        </div>

        {{-- Primary services (2 cards) --}}
        <h2 class="luxe-section-title luxe-reveal" data-i18n="services_primary">Our Primary Services</h2>
        <p class="luxe-section-sub luxe-reveal" data-i18n="services_sub">Transport and storage at Paris CDG & Orly</p>
        <div class="luxe-grid-2">
            <div class="luxe-card luxe-reveal luxe-reveal-delay-1 luxe-shine-wrap">
                <h3 data-i18n="service_transport">Transport of Luggage</h3>
                <p data-i18n="service_transport_desc">Door-to-door or airport transfer for your bags. Reliable and tracked.</p>
                <a href="{{ $formUrl }}" data-i18n="read_more">Read more →</a>
            </div>
            <div class="luxe-card luxe-reveal luxe-reveal-delay-2 luxe-shine-wrap">
                <h3 data-i18n="service_storage">Luggage Storage</h3>
                <p data-i18n="service_storage_desc">Store your bags at our counters at CDG Terminal 2 and Orly Terminal 3. Secure and flexible.</p>
                <a href="{{ $formUrl }}" data-i18n="read_more">Read more →</a>
            </div>
        </div>

        {{-- Service cards with images (4) — 4e carte centrée --}}
        <div class="luxe-grid-2 luxe-grid-4-services" style="margin-top: 3rem;">
            <div class="luxe-service-card luxe-reveal luxe-shine-wrap">
                <div class="luxe-service-img luxe-img-reveal">
                    <img src="{{ asset('images/left-luggage.jpg') }}" alt="Left luggage" loading="lazy">
                </div>
                <div class="luxe-service-body">
                    <h3 data-i18n="svc_left">Left Luggage Facilities</h3>
                    <p data-i18n="svc_left_desc">Secure storage at the airport so you can explore Paris hands-free.</p>
                    <a href="{{ $formUrl }}" data-i18n="read_more">Read more →</a>
                </div>
            </div>
            <div class="luxe-service-card luxe-reveal luxe-reveal-delay-1 luxe-shine-wrap">
                <div class="luxe-service-img luxe-img-reveal">
                    <img src="{{ asset('images/airport-beijing.jpg') }}" alt="Lost items" loading="lazy">
                </div>
                <div class="luxe-service-body">
                    <h3 data-i18n="svc_lost">Lost Items</h3>
                    <p data-i18n="svc_lost_desc">We help recover lost luggage and items at the airport.</p>
                    <a href="{{ $faqUrl }}" data-i18n="read_more">Read more →</a>
                </div>
            </div>
            <div class="luxe-service-card luxe-reveal luxe-reveal-delay-2 luxe-shine-wrap">
                <div class="luxe-service-img luxe-img-reveal">
                    <img src="{{ asset('images/airport-paris.jpg') }}" alt="Children's equipment" loading="lazy">
                </div>
                <div class="luxe-service-body">
                    <h3 data-i18n="svc_children">Children's Equipment</h3>
                    <p data-i18n="svc_children_desc">Strollers and family-friendly services for a smooth journey.</p>
                    <a href="{{ $formUrl }}" data-i18n="read_more">Read more →</a>
                </div>
            </div>
            <div class="luxe-service-card luxe-reveal luxe-reveal-delay-3 luxe-shine-wrap">
                <div class="luxe-service-img luxe-img-reveal">
                    <img src="{{ asset('images/airport-jfk.jpg') }}" alt="High tech equipment" loading="lazy">
                </div>
                <div class="luxe-service-body">
                    <h3 data-i18n="svc_tech">High Tech Equipment</h3>
                    <p data-i18n="svc_tech_desc">SIM cards, pocket Wi‑Fi and tech rentals at the airport.</p>
                    <a href="{{ $formUrl }}" data-i18n="read_more">Read more →</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Our Process --}}
    <section class="luxe-section-process">
        <div class="luxe-process-wrap">
            <h2 class="luxe-section-title luxe-reveal" data-i18n="process_title">Our Process</h2>
            <p class="luxe-section-sub luxe-reveal" data-i18n="process_sub">Your luggage experience, our priority</p>
            <div class="luxe-process">
                <div class="luxe-process-item luxe-reveal">
                    <div class="luxe-process-num">1</div>
                    <h3 data-i18n="process_1">Reserve</h3>
                    <p data-i18n="process_1_desc">Complete your reservation in just a few steps through our secure platform. Your digital voucher is instantly issued and accessible from your email and personal account.</p>
                </div>
                <div class="luxe-process-item luxe-reveal luxe-reveal-delay-1">
                    <div class="luxe-process-num">2</div>
                    <h3 data-i18n="process_2">Choose your service</h3>
                    <p data-i18n="process_2_desc">Decide how your luggage is handled. Drop off at our dedicated airport facility, benefit from a personalised meet & collect service, or arrange coordinated transport to or from the airport.</p>
                </div>
                <div class="luxe-process-item luxe-reveal luxe-reveal-delay-2">
                    <div class="luxe-process-num">3</div>
                    <h3 data-i18n="process_3">Travel with confidence</h3>
                    <p data-i18n="process_3_desc">Move freely while we take care of your luggage. Handled with professionalism, discretion and the highest standards of security, from start to finish.</p>
                </div>
            </div>
            <p class="luxe-process-cta">
                <a href="{{ $formUrl }}" class="btn-cta" data-i18n="nav_book">Book now</a>
            </p>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="luxe-section">
        <h2 class="luxe-section-title luxe-reveal" data-i18n="testimonial_title">Trusted by Worldwide Travelers</h2>
        <p class="luxe-section-sub luxe-reveal" data-i18n="testimonial_sub">From first-time customers to our most loyal commuters, see why people love the service of Hello Passenger.</p>
        <div class="luxe-testimonials-grid luxe-testimonials-4">
            <div class="luxe-testimonial-card luxe-reveal">
                <blockquote data-i18n="testimonial_1">Had a very positive experience. Left 6/7 items of luggage including a computer and other stuff and it cost around €60 for 4/5 hours — long enough to take the train into town for a walk. Friendly and efficient service.</blockquote>
                <div class="luxe-testimonial-meta">
                    <img src="{{ asset('images/testimonial-4.png') }}" alt="" class="luxe-testimonial-avatar" loading="lazy">
                    <div>
                        <div class="luxe-testimonial-name">Deep Sandhu</div>
                        <div class="luxe-testimonial-date">06 Nov 22</div>
                    </div>
                </div>
            </div>
            <div class="luxe-testimonial-card luxe-reveal luxe-reveal-delay-1">
                <blockquote data-i18n="testimonial_2">Professional, trustworthy and reasonably priced. They returned our lost luggage to us quickly and easily. We are grateful for this organization and the people who work here.</blockquote>
                <div class="luxe-testimonial-meta">
                    <img src="{{ asset('images/testimonial-1.png') }}" alt="" class="luxe-testimonial-avatar" loading="lazy">
                    <div>
                        <div class="luxe-testimonial-name">Paul Franzen</div>
                        <div class="luxe-testimonial-date">02 Nov 25</div>
                    </div>
                </div>
            </div>
            <div class="luxe-testimonial-card luxe-reveal luxe-reveal-delay-2">
                <blockquote data-i18n="testimonial_3">This place is great for storing your luggage while you take a trip into Paris Central. Bags are weighed and charged by weight and time. The premises are well organised, the staff are very well organised. We collected our bags earlier than planned and got a refund.</blockquote>
                <div class="luxe-testimonial-meta">
                    <img src="{{ asset('images/testimonial-2.png') }}" alt="" class="luxe-testimonial-avatar" loading="lazy">
                    <div>
                        <div class="luxe-testimonial-name">David Smythe</div>
                        <div class="luxe-testimonial-date">05 Apr 25</div>
                    </div>
                </div>
            </div>
            <div class="luxe-testimonial-card luxe-reveal luxe-reveal-delay-3">
                <blockquote data-i18n="testimonial_4">Straightforward bag storage and pickup to free you for a day out in Paris without worrying about luggage.</blockquote>
                <div class="luxe-testimonial-meta">
                    <img src="{{ asset('images/testimonial-3.png') }}" alt="" class="luxe-testimonial-avatar" loading="lazy">
                    <div>
                        <div class="luxe-testimonial-name">Eric Auchard</div>
                        <div class="luxe-testimonial-date">05 Oct 25</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Trust badges --}}
        <div class="luxe-trust luxe-reveal">
            <div class="luxe-trust-item" data-i18n="trust_reviews"><strong>Rating of 4.3/5</strong> — Google Reviews</div>
            <div class="luxe-trust-item" data-i18n="trust_benefits"><strong>Exclusive online benefits</strong></div>
            <div class="luxe-trust-item" data-i18n="trust_secure"><strong>100% secure payment</strong></div>
            <div class="luxe-trust-item" data-i18n="trust_clickcollect"><strong>FREE Click & Collect</strong></div>
        </div>
    </section>

@endsection
