<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== BDM Configuration Check ===\n\n";

echo "BDM_EMAIL:    " . var_export(config('services.bdm.email'), true) . "\n";
echo "BDM_USERNAME: " . var_export(config('services.bdm.username'), true) . "\n";
echo "BDM_PASSWORD: " . var_export(config('services.bdm.password'), true) . "\n";
echo "BDM_BASE_URL: " . var_export(config('services.bdm.base_url'), true) . "\n";

echo "\n=== .env file check ===\n";
$envContent = file_get_contents(__DIR__ . '/.env');
if (strpos($envContent, 'BDM_API_EMAIL') !== false) {
    echo "✅ BDM_API_EMAIL found in .env\n";
    preg_match('/BDM_API_EMAIL=(.*)/m', $envContent, $matches);
    echo "   Value: " . ($matches[1] ?? 'NOT SET') . "\n";
} else {
    echo "❌ BDM_API_EMAIL NOT found in .env\n";
}
