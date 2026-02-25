@extends('layouts.front')

@section('title', 'FAQ & Help Center — Hello Passenger')
@section('meta_description', 'Frequently asked questions about luggage storage, transport, and services at Paris CDG and Orly airports.')

@section('content')
<div class="luxe-page">
    <h1 data-i18n="faq_title" class="luxe-reveal">Help Center</h1>
    <p class="text-lg opacity-90 luxe-reveal luxe-reveal-delay-1" data-i18n="faq_subtitle">Answers to your questions</p>

    <section class="luxe-page-card luxe-reveal luxe-shine-wrap">
        <h2 data-i18n="faq_info_title">Information and timetables</h2>
        <p data-i18n="faq_info_text">During a layover at <strong class="text-white">Paris Charles de Gaulle (CDG)</strong> or <strong class="text-white">Paris Orly (ORY) airport</strong>, traveling light makes all the difference. <strong class="text-white">Hello Passenger</strong> provides secure luggage handling, transport services, and dedicated support at Paris airports.</p>
        <p class="text-sm" data-i18n="faq_info_sub">Below you’ll find answers to the most frequently asked questions about luggage storage at Paris airports, airport transit, lost baggage, and passenger services.</p>
    </section>

    <section class="luxe-page-card luxe-reveal luxe-shine-wrap luxe-reveal-delay-1">
        <h2 data-i18n="faq_what_title">What to do</h2>
        <p data-i18n="faq_what_text">Plan your luggage with Hello Passenger: <strong class="text-white">reserve</strong> on our secure platform, <strong class="text-white">choose your service</strong> (airport drop-off, meet &amp; collect, or transport), then <strong class="text-white">travel with confidence</strong> while we handle your bags. Your digital voucher is issued instantly and is available from your email and personal account.</p>
        <ul style="padding-left: 1.25rem; margin: 0.5rem 0 0;">
            <li data-i18n="faq_what_1">Complete your reservation in a few steps.</li>
            <li data-i18n="faq_what_2">Select how your luggage is handled (drop-off at our facility, meet &amp; collect, or coordinated transport).</li>
            <li data-i18n="faq_what_3">Drop off or hand over your luggage at the agreed time and place.</li>
            <li data-i18n="faq_what_4">Collect your luggage when you return or have it delivered as arranged.</li>
        </ul>
    </section>

    <h2 style="margin-top: 2rem;" class="luxe-reveal" data-i18n="faq_faq_title">Frequently asked questions</h2>
    <div class="luxe-faq luxe-reveal luxe-reveal-delay-1">
        <details>
            <summary data-i18n="faq_q1">What should I do if my baggage is lost at the airport?</summary>
            <div><p>Air travel is very reliable, but baggage loss can occasionally occur. If your luggage is missing upon arrival at Paris Charles de Gaulle (CDG) or Paris Orly (ORY), you must first contact your airline’s baggage service at the airport. A claim file will be created in your name so the airline can locate and return your baggage as quickly as possible. If you have already left the airport, you generally have up to 48 hours after landing to report missing luggage.</p></div>
        </details>
        <details>
            <summary>Can I be compensated for lost baggage?</summary>
            <div><p>Yes. If your luggage is officially declared lost, you may be entitled to compensation. You must submit a written claim to the airline within 21 days of the loss. Under international agreements, compensation can reach up to approximately €1,300, depending on the airline and circumstances. Please note that jewelry, cash, and valuables are not reimbursed, as they should be kept in hand luggage.</p></div>
        </details>
        <details>
            <summary>Do I need to collect my luggage between two flights?</summary>
            <div><p>Baggage claim between two flights is possible for a connection at Charles-de-Gaulle airport, whether you have booked two flights with the same airline or with two different airlines. Collecting your luggage at the connecting airport before boarding your next flight is the safest way to avoid losing it. If your flights are operated by different airlines, your luggage is not transferred automatically—you must collect it at the transit airport and check it in again.</p></div>
        </details>
        <details>
            <summary>What visa is required to transit through a Paris airport?</summary>
            <div><p>Visa requirements depend on your origin, destination, and nationality: Schengen to Schengen: no passport control; Entering the Schengen Area: passport control required; Leaving the Schengen Area: exit passport control required; Non-Schengen to non-Schengen: security check only, unless you leave the transit area.</p></div>
        </details>
        <details>
            <summary>What items are not allowed in airport luggage storage?</summary>
            <div><p>Prohibited items include explosive, flammable, or dangerous materials such as ammunition, fireworks, and other pyrotechnic articles. All stored luggage is X-ray screened before acceptance.</p></div>
        </details>
        <details>
            <summary>What items are allowed in airport luggage storage?</summary>
            <div><p>Hello Passenger allows you to drop various luggage items at the airport: accessories, cabin luggage, checked luggage or oversized items. We have a secure cloakroom. Sporting goods such as skis, fishing equipment, motorcycle helmets are accepted.</p></div>
        </details>
        <details>
            <summary>Can I store food in luggage storage at the airport?</summary>
            <div><p>Yes, food can be stored at Paris airports, provided it is properly packed. Perishable food: short-term storage only. Non-perishable food: longer storage allowed. Hello Passenger reserves the right to refuse smelly or poorly packaged food.</p></div>
        </details>
        <details>
            <summary>What payment methods does Hello Passenger accept?</summary>
            <div><p>Hello Passenger accepts Visa and Mastercard via secure online payment, as well as bank cards and cash directly at the agency. Prices depend on the type of luggage and the duration of the deposit.</p></div>
        </details>
        <details>
            <summary>Where can I find Hello Passenger at the airport?</summary>
            <div><p><strong>Paris CDG Airport:</strong> Terminal 2 TGV Railway station – Level 4, opposite Sheraton Hotel, between Terminal 2C and 2E.<br><strong>Paris ORLY Airport:</strong> Terminal 3, Arrival level.</p></div>
        </details>
        <details>
            <summary>What are the operating hours of the luggage storage?</summary>
            <div><p><strong>7:00 a.m. to 9:00 p.m., Monday to Sunday.</strong></p></div>
        </details>
        <details>
            <summary>How secure is luggage storage?</summary>
            <div><p>Hello Passenger offers a high level of security: identity verification (passport or ID required), X-ray screening, registered and tagged luggage, and video-monitored storage areas.</p></div>
        </details>
        <details>
            <summary>Can Hello Passenger transport my luggage at CDG?</summary>
            <div><p>Yes. Hello Passenger offers door-to-door luggage transport: on arrival—luggage collected at CDG and delivered to your accommodation; before departure—luggage collected from your home and delivered to CDG.</p></div>
        </details>
    </div>

    <p style="text-align: center; margin-top: 2rem;" class="luxe-reveal">
        <a href="{{ route('form-consigne') }}" data-i18n="back_home">← Back to home</a>
        <span class="opacity-50 mx-2">·</span>
        <a href="{{ route('about-us') }}" data-i18n="nav_about">About Us</a>
    </p>
</div>
@endsection
