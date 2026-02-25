<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$photos = \App\Models\BagagePhoto::with('commande')->take(10)->get();

echo "=== PHOTOS EN BASE ===\n";
foreach($photos as $photo) {
    echo "Photo ID: {$photo->id}\n";
    echo "  Commande ID: {$photo->commande_id}\n";
    echo "  Type: {$photo->type}\n";
    echo "  Path: {$photo->photo_path}\n";
    echo "  URL: {$photo->photo_url}\n";
    echo "  Exists: " . (file_exists(storage_path('app/public/' . $photo->photo_path)) ? 'YES' : 'NO') . "\n";
    echo "---\n";
}
