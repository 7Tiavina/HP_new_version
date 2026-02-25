<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BagagePhoto extends Model
{
    protected $fillable = [
        'commande_id',
        'type',
        'photo_path',
        'agent_id',
        'notes',
    ];

    /**
     * Get the commande that owns the photo.
     */
    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    /**
     * Get the agent who took the photo.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the full URL to the photo.
     */
    public function getPhotoUrlAttribute(): string
    {
        if (empty($this->photo_path)) {
            return '';
        }
        
        // Try multiple possible paths
        $possiblePaths = [
            storage_path('app/public/' . $this->photo_path),
            storage_path('app/private/public/' . $this->photo_path),
            public_path('storage/' . $this->photo_path),
        ];
        
        // Check if file exists in any location
        $fileExists = false;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $fileExists = true;
                // If in private, try to copy to public
                if (strpos($path, 'private') !== false) {
                    $publicPath = storage_path('app/public/' . $this->photo_path);
                    $publicDir = dirname($publicPath);
                    if (!file_exists($publicDir)) {
                        @mkdir($publicDir, 0755, true);
                    }
                    @copy($path, $publicPath);
                }
                break;
            }
        }
        
        // Use Storage facade to get the correct URL
        $url = \Illuminate\Support\Facades\Storage::url($this->photo_path);
        
        // If file doesn't exist, return placeholder or log error
        if (!$fileExists) {
            \Illuminate\Support\Facades\Log::warning('Photo file not found', [
                'photo_id' => $this->id,
                'photo_path' => $this->photo_path,
                'generated_url' => $url
            ]);
        }
        
        return $url;
    }
}
