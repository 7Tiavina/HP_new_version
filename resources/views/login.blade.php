<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Agents-HelloPassenger</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon-hellopassenger.png') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    window.onload = () => {
      lucide.createIcons();
    };
  </script>
</head>

<body class="min-h-screen bg-gradient-to-br from-yellow-400 to-yellow-500 relative overflow-hidden">

  <!-- Clouds decoration -->
  <div class="absolute top-20 left-10 opacity-20">
    <i data-lucide="cloud" class="w-16 h-16 text-white"></i>
  </div>
  <div class="absolute top-32 right-20 opacity-15">
    <i data-lucide="cloud" class="w-20 h-20 text-white"></i>
  </div>
  <div class="absolute top-40 left-1/3 opacity-10">
    <i data-lucide="cloud" class="w-24 h-24 text-white"></i>
  </div>

  <!-- Luggage decorations -->
  <div class="absolute bottom-20 left-8 opacity-20">
    <i data-lucide="luggage" class="w-24 h-24 text-white"></i>
  </div>
  <div class="absolute bottom-32 right-12 opacity-15">
    <i data-lucide="luggage" class="w-28 h-28 text-white"></i>
  </div>
  <div class="absolute bottom-40 left-1/4 opacity-10">
    <i data-lucide="luggage" class="w-20 h-20 text-white"></i>
  </div>

  <!-- Header -->
  <header class="relative z-10 flex items-center justify-between p-6">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
        <i data-lucide="map-pin" class="w-6 h-6 text-yellow-500"></i>
      </div>
      <span class="text-xl font-semibold text-gray-800">HelloPassenger</span>
    </div>
    <div class="flex items-center gap-4">
      <button class="bg-gray-800 text-white hover:bg-gray-700 rounded-full px-6 py-2">Espace Clients</button>
      <button class="text-gray-800 hover:bg-white/20 px-4 py-2 rounded-lg flex items-center">
        <i data-lucide="menu" class="w-5 h-5"></i><span class="ml-2">MENU</span>
      </button>
    </div>
  </header>

  <!-- Main content -->
  <main class="flex items-center justify-center px-6 py-12 relative z-10">
    <div class="w-full max-w-md">

      <!-- Welcome message -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-semibold text-gray-800 mb-2">Connectez-vous à HelloPassenger</h1>
        <p class="text-gray-700">Espace dédié uniquements aux Staff de HelloPassenger !</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white/95 backdrop-blur-sm shadow-lg rounded-xl p-6 space-y-6">
        <div class="text-center pb-4">
          <h2 class="text-xl text-gray-800 font-semibold">Connexion</h2>
        </div>

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-6">
          @csrf
          <div class="space-y-4">
            <div>
              <label for="email" class="block text-gray-700 mb-1">Adresse e-mail</label>
              <input type="email" id="email" name="email" placeholder="votre@email.com"
                      class="w-full bg-gray-50 border border-gray-200 focus:border-yellow-400 focus:ring-yellow-400 rounded px-4 py-2">       
            </div>

            <div>
              <label for="password" class="block text-gray-700 mb-1">Mot de passe</label>
              <input type="password" id="password" name="password" placeholder="••••••••"
                      class="w-full bg-gray-50 border border-gray-200 focus:border-yellow-400 focus:ring-yellow-400 rounded px-4 py-2">       
            </div>
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-gray-600">
              <input type="checkbox" class="border-gray-300 rounded" />
              Se souvenir de moi
            </label>
            <a href="#" class="text-yellow-600 hover:text-yellow-700">Mot de passe oublié ?</a>
          </div>

          <button type="submit" class="w-full bg-gray-800 text-white hover:bg-gray-700 rounded-lg py-3">
            Se connecter
          </button>

        </form>
      </div>

      <!-- Conditions -->
      <div class="mt-8 text-center">
        <p class="text-sm text-gray-700">
          En vous connectant, vous acceptez nos
          <a href="#" class="text-yellow-600 hover:text-yellow-700">conditions d'utilisation</a>
        </p>
      </div>
    </div>
  </main>
</body>
</html>
