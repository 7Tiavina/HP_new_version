<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'HelloPassenger Admin')</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
  <link rel="icon" type="image/png" href="{{ asset('favicon-hellopassenger.png') }}">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            yellow: {400: '#fbbf24', 500: '#f59e0b'}
          }
        }
      }
    }
  </script>
  <script src="{{ asset('js/dashboard.js') }}" defer></script>
  
</head>
<body class="min-h-screen bg-gray-50">
<header class="bg-white border-b border-gray-200 px-6 py-4 dashboard-header">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-yellow-400 rounded-lg flex items-center justify-center">
                <i class="fas fa-map-marker-alt text-gray-800"></i>
            </div>
            <div>
                <h1 class="text-xl font-semibold text-gray-800">HelloPassenger Admin</h1>
                <p class="text-sm text-gray-600">Tableau de bord administrateur</p>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="relative">
                <button class="p-2 rounded-full hover:bg-gray-100 relative">
                    <i class="fas fa-bell text-gray-700"></i>
                    <span class="absolute top-1 right-1 w-3 h-3 bg-red-500 rounded-full text-xs flex items-center justify-center text-white">
                        3
                    </span>
                </button>
            </div>
            <button class="p-2 rounded-full hover:bg-gray-100">
                <i class="fas fa-cog text-gray-700"></i>
            </button>
            @php
                $userId = session('user_id');
                $user = $userId ? \App\Models\User::find($userId) : null;
                $userInitials = $user ? strtoupper(substr($user->email, 0, 2)) : 'AD';
                $userEmail = $user ? $user->email : 'admin@hello.com';
            @endphp
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-yellow-400 flex items-center justify-center text-gray-800 font-medium">
                    {{ $userInitials }}
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-800">Admin</p>
                    <p class="text-xs text-gray-600">{{ $userEmail }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="p-2 rounded-full hover:bg-gray-100">
                  <i class="fas fa-sign-out-alt text-gray-700"></i>
                </button>
              </form>
        </div>
    </div>
</header>