<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Smalot\PdfParser\Parser;

class Commande extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commandes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'client_email',
        'client_nom',
        'client_prenom',
        'client_telephone',
        'client_civilite',
        'client_nom_societe',
        'client_adresse',
        'client_complement_adresse',
        'client_ville',
        'client_code_postal',
        'client_pays',
        'id_api_commande',
        'id_plateforme',
        'total_prix_ttc',
        'statut',
        'details_commande_lignes',
        'invoice_content',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'details_commande_lignes' => 'array',
        'total_prix_ttc' => 'decimal:2',
    ];

    /**
     * Get the client that owns the Commande.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Get the payment record associated with the Commande.
     */
    public function paymentClient()
    {
        return $this->hasOne(PaymentClient::class);
    }

    /**
     * Get all photos for this commande.
     */
    public function photos()
    {
        return $this->hasMany(BagagePhoto::class);
    }

    /**
     * Get depot photos.
     */
    public function depotPhotos()
    {
        return $this->hasMany(BagagePhoto::class)->where('type', 'depot');
    }

    /**
     * Get restitution photos.
     */
    public function restitutionPhotos()
    {
        return $this->hasMany(BagagePhoto::class)->where('type', 'restitution');
    }

    /**
     * Extract invoice number from the PDF invoice content
     * Returns the invoice number as it appears in the PDF (e.g., F-ORY-123 or F-CDG-456)
     */
    public function getInvoiceNumberFromPdf(): ?string
    {
        if (!$this->invoice_content) {
            return null;
        }

        try {
            $pdfContent = base64_decode($this->invoice_content);
            if ($pdfContent === false) {
                return null;
            }

            $parser = new Parser();
            $pdf = $parser->parseContent($pdfContent);
            $text = $pdf->getText();

            // Search for invoice number patterns: F-ORY-xxx or F-CDG-xxx
            // Also try to find "Facture n°:" or "Invoice No.:" followed by the number
            $patterns = [
                '/F-ORY-[\w-]+/i',
                '/F-CDG-[\w-]+/i',
                '/(?:Facture n°|Invoice No\.?)[\s:]+(F-ORY-[\w-]+|F-CDG-[\w-]+|[\w-]+)/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $text, $matches)) {
                    // If we found a match with prefix, return it
                    if (isset($matches[1]) && (strpos($matches[1], 'F-ORY-') === 0 || strpos($matches[1], 'F-CDG-') === 0)) {
                        return $matches[1];
                    }
                    // If the full match has the prefix, return it
                    if (strpos($matches[0], 'F-ORY-') === 0 || strpos($matches[0], 'F-CDG-') === 0) {
                        return $matches[0];
                    }
                }
            }

            // Fallback: try to find any number after "Facture n°" or "Invoice"
            if (preg_match('/(?:Facture n°|Invoice No\.?)[\s:]+([A-Z0-9-]+)/i', $text, $matches)) {
                return trim($matches[1]);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error extracting invoice number from PDF', [
                'commande_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Extract QR code from PDF (base64) and return as PNG base64
     * QR code position: TOP-CENTER of BDM invoice
     */
    public function getQrCodeFromPdf(): ?string
    {
        if (!$this->invoice_content) {
            return null;
        }

        try {
            $pdfContent = base64_decode($this->invoice_content);
            if ($pdfContent === false) {
                return null;
            }

            $tempDir = storage_path('app/temp/qr-' . $this->id);
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $pdfPath = $tempDir . '/invoice.pdf';
            $qrPath = $tempDir . '/qr-code.png';
            
            file_put_contents($pdfPath, $pdfContent);
            $qrCodeBase64 = null;

            // Method 1: ImageMagick PHP
            if (class_exists('Imagick')) {
                $qrCodeBase64 = $this->extractQrImagick($pdfPath, $qrPath, $tempDir);
            }

            // Method 2: pdftoppm CLI
            if (!$qrCodeBase64) {
                $qrCodeBase64 = $this->extractQrPdftoppm($pdfPath, $qrPath, $tempDir);
            }

            $this->cleanupTempDir($tempDir);
            return $qrCodeBase64;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('QR extraction failed', [
                'commande_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Extract QR using ImageMagick PHP extension
     * QR position: TOP-CENTER (30% from left, 2% from top)
     */
    private function extractQrImagick(string $pdfPath, string $qrPath, string $tempDir): ?string
    {
        try {
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($pdfPath . '[0]');
            $imagick->setImageFormat('png');
            
            $width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();
            
            // QR code position for BDM invoices (TOP-CENTER)
            $qrX      = (int) ($width * 0.30);
            $qrY      = (int) ($height * 0.02);
            $qrWidth  = (int) ($width * 0.25);
            $qrHeight = (int) ($height * 0.20);
            
            $imagick->cropImage($qrWidth, $qrHeight, $qrX, $qrY);
            $imagick->writeImage($qrPath);
            
            $qrCodeBase64 = base64_encode(file_get_contents($qrPath));
            $imagick->clear();
            $imagick->destroy();
            
            return $qrCodeBase64;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract QR using pdftoppm command
     * QR position: TOP-CENTER (30% from left, 2% from top)
     */
    private function extractQrPdftoppm(string $pdfPath, string $qrPath, string $tempDir): ?string
    {
        $command = sprintf('pdftoppm -r 150 -png -f 1 -l 1 %s %s 2>&1', 
            escapeshellarg($pdfPath), 
            escapeshellarg($tempDir . '/invoice')
        );
        
        exec($command, $output, $returnCode);
        
        $pngPath = $tempDir . '/invoice-1.png';
        if ($returnCode !== 0 || !file_exists($pngPath)) {
            return null;
        }

        $imageInfo = @getimagesize($pngPath);
        if (!$imageInfo) return null;

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $sourceImage = imagecreatefrompng($pngPath);
        if (!$sourceImage) return null;

        // QR code position for BDM invoices (TOP-CENTER)
        $qrX      = (int) ($width * 0.30);
        $qrY      = (int) ($height * 0.02);
        $qrWidth  = (int) ($width * 0.25);
        $qrHeight = (int) ($height * 0.20);

        $qrImage = imagecrop($sourceImage, [
            'x' => $qrX, 'y' => $qrY,
            'width' => $qrWidth, 'height' => $qrHeight
        ]);
        imagedestroy($sourceImage);

        if (!$qrImage) return null;

        imagepng($qrImage, $qrPath);
        imagedestroy($qrImage);

        return base64_encode(file_get_contents($qrPath));
    }

    /**
     * Clean up temp directory
     */
    private function cleanupTempDir(string $dir): void
    {
        if (!file_exists($dir)) return;
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
        rmdir($dir);
    }

    /**
     * Get formatted reference with airport prefix
     */
    public function getFormattedReference(): string
    {
        $baseRef = $this->id_api_commande ?? $this->paymentClient->monetico_order_id ?? $this->id;
        
        $orlyAirportId = '64f00ace-31b6-45b0-bcb2-b562b1ac08d9';
        $cdgAirportId = '88bb89e0-b966-4420-9ed3-7a6745e4d947';
        $airportId = $this->id_plateforme ?? null;
        
        if ($airportId === $orlyAirportId) {
            return 'F-ORY-' . $baseRef;
        } elseif ($airportId === $cdgAirportId) {
            return 'F-CDG-' . $baseRef;
        }
        
        return $baseRef;
    }
}
