@extends('layouts.front')

@section('title', 'Mon Profil — Hello Passenger')

@push('styles')
    <script>window.tailwind=window.tailwind||{};window.tailwind.config={corePlugins:{preflight:false},important:'#client-page-root'};</script>
    <script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div id="client-page-root" class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900" data-i18n="profile_title">Mon Profil</h1>
                <p class="text-gray-600 mt-2" data-i18n="profile_subtitle">Gérez vos informations personnelles</p>
            </div>
            <a href="{{ route('client.dashboard') }}" class="flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors" data-i18n="btn_back">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold" data-i18n="success">Succès!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold" data-i18n="error">Erreur!</strong>
                <ul class="list-disc list-inside mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('client.update-profile') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="prenom" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="{{ old('prenom', $client->prenom) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" required>
                    </div>
                    <div>
                        <label for="nom" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_nom">Nom</label>
                        <input type="text" id="nom" name="nom" value="{{ old('nom', $client->nom) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" required>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_email">Email</label>
                        <input type="email" id="email" value="{{ $client->email }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed" disabled>
                        <p class="text-xs text-gray-500 mt-1" data-i18n="profile_email_note">L'email ne peut pas être modifié</p>
                    </div>
                    <div>
                        <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_telephone">Téléphone mobile</label>
                        <input type="tel" id="telephone" name="telephone" value="{{ old('telephone', $client->telephone) }}" placeholder="+33 6 12 34 56 78 (avec code pays)" data-i18n-placeholder="placeholder_telephone" autocomplete="off" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        <p class="text-xs text-gray-500 mt-1" data-i18n="phone_country_code_hint">⚠️ Veuillez renseigner votre numéro avec le code pays (ex: +33 pour la France, +230 pour Maurice)</p>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6 mt-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4" data-i18n="address_section">Adresse</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="adresse" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_adresse">Adresse</label>
                            <input type="text" id="adresse" name="adresse" value="{{ old('adresse', $client->adresse) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label for="complementAdresse" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_complement_adresse">Complément d'adresse</label>
                            <input type="text" id="complementAdresse" name="complementAdresse" value="{{ old('complementAdresse', $client->complementAdresse) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label for="ville" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_ville">Ville</label>
                            <input type="text" id="ville" name="ville" value="{{ old('ville', $client->ville) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label for="pays" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_pays">Pays</label>
                            <input type="text" id="pays" name="pays" value="{{ old('pays', $client->pays) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="submit" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-semibold transition-colors" data-i18n="btn_save">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
