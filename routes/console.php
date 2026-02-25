<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('hp:bootstrap-staff', function () {
    $adminEmail = 'mahnish@blablabla-agency.com';
    $agentEmail = 'aksh9106@gmail.com';

    $this->info('Bootstrapping staff users...');

    $created = [];

    $ensure = function (string $email, string $role) use (&$created) {
        /** @var User|null $existing */
        $existing = User::where('email', $email)->first();
        if ($existing) {
            // Ensure role is correct (do not rotate password silently)
            if (($existing->role ?? null) !== $role) {
                $existing->role = $role;
                $existing->save();
            }
            return ['user' => $existing, 'password' => null, 'created' => false];
        }

        $password = Str::random(16);
        $hash = Hash::make($password);
        $local = explode('@', $email)[0] ?? 'User';
        $data = [
            'email' => $email,
            'password_hash' => $hash,
            'role' => $role,
        ];
        if (Schema::hasColumn('users', 'name')) {
            $data['name'] = ucfirst(substr((string)$local, 0, 50));
        }
        if (Schema::hasColumn('users', 'password')) {
            $data['password'] = $hash;
        }

        $user = User::create($data);
        $created[] = $email;
        return ['user' => $user, 'password' => $password, 'created' => true];
    };

    $admin = $ensure($adminEmail, 'admin');
    $agent = $ensure($agentEmail, 'agent');

    $this->line('');
    $this->info('Results:');

    foreach ([
        ['label' => 'Admin', 'email' => $adminEmail, 'res' => $admin],
        ['label' => 'Agent', 'email' => $agentEmail, 'res' => $agent],
    ] as $row) {
        $this->line($row['label'] . ': ' . $row['email']);
        if ($row['res']['created']) {
            $this->warn('  Created. Password: ' . $row['res']['password']);
        } else {
            $this->line('  Already exists. (Password unchanged)');
        }
    }

    $this->line('');
    $this->comment('Login routes:');
    $this->line('- Admin: /login');
    $this->line('- Agent: /agent/login');
})->purpose('Create default admin + agent users');
