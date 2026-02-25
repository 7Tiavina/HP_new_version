<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - HelloPassenger</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<div class="min-h-screen bg-gray-dark flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <img class="mx-auto h-32 w-auto" src="{{ asset('HP-Logo-White.png') }}" alt="HelloPassenger">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
                Connexion
            </h2>
            <p class="mt-2 text-center text-sm text-gray-400">
                Connectez-vous à votre compte
            </p>
        </div>
        <form class="mt-8 space-y-6" method="POST" action="{{ route('auth.login.submit') }}">
            @csrf
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-600 bg-gray-800 text-white placeholder-gray-400 focus:outline-none focus:ring-yellow-custom focus:border-yellow-custom focus:z-10 sm:text-sm" placeholder="Votre email" value="{{ old('email') }}">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Mot de passe</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-600 bg-gray-800 text-white placeholder-gray-400 focus:outline-none focus:ring-yellow-custom focus:border-yellow-custom focus:z-10 sm:text-sm" placeholder="Votre mot de passe">
                </div>
            </div>

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Erreur de connexion
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-gray-dark bg-yellow-custom hover:bg-yellow-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-custom">
                    Se connecter
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
