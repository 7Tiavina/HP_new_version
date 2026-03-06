@php
    $lang = session('app_language', 'fr');
    $baseRef = $commande->id_api_commande ?? $commande->paymentClient->monetico_order_id ?? $commande->id;
    $orlyAirportId = '64f00ace-31b6-45b0-bcb2-b562b1ac08d9';
    $cdgAirportId = '88bb89e0-b966-4420-9ed3-7a6745e4d947';
    $airportId = $commande->id_plateforme ?? null;
    if ($airportId === $orlyAirportId) { $invoiceRef = 'F-ORY-' . $baseRef; }
    elseif ($airportId === $cdgAirportId) { $invoiceRef = 'F-CDG-' . $baseRef; }
    else { $invoiceRef = $baseRef; }
    
    $isEn = $lang === 'en';
@endphp

@extends('layouts.front')

@section('title', 'Paiement réussi — Hello Passenger')

@push('styles')
    <script>window.tailwind=window.tailwind||{};window.tailwind.config={corePlugins:{preflight:false},important:'#payment-success-root'};</script>
    <script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div id="payment-success-root" class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-white p-8 rounded-lg shadow-lg text-center border border-gray-200">
            <div class="mx-auto bg-green-100 rounded-full h-16 w-16 flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2" data-i18n="success_title">Paiement réussi !</h1>
            <p class="text-gray-600 mb-6" data-i18n="success_subtitle">Votre commande a été confirmée et votre facture a été générée.</p>
            
            <!-- Email notification message -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-gray-700">
                    {{ $isEn 
                        ? 'Thank you for your order! A confirmation email with your invoice has been sent to ' . $commande->client_email 
                        : 'Merci pour votre commande ! Un email de confirmation avec votre facture a été envoyé à ' . $commande->client_email
                    }}
                </p>
            </div>
            
            <div class="flex justify-center space-x-4 mb-8">
                <a href="{{ route('invoices.show', ['id' => $commande->id, 'lang' => $lang]) }}"
                   download="{{ $invoiceRef }}.pdf"
                   id="download-invoice-link"
                   class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 flex items-center justify-center">
                    <span data-i18n="success_download">Télécharger ma facture</span>
                </a>
            </div>
            <div class="bg-gray-100 p-4 rounded-lg border border-gray-200">
                <h2 class="text-xl font-semibold text-left mb-4" data-i18n="success_preview">Aperçu de la facture</h2>
                <div class="w-full h-[80vh] border rounded-md bg-white">
                    <iframe id="invoice-iframe" src="{{ route('invoices.show', ['id' => $commande->id, 'lang' => $lang]) }}" class="w-full h-full" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
