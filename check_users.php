<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = \App\Models\User::all(['id', 'email', 'role']);

echo "=== LISTE DES UTILISATEURS ===\n";
foreach($users as $user) {
    echo "ID: {$user->id} | Email: {$user->email} | Role: {$user->role}\n";
}
