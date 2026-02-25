<?php
/**
 * Debug file - minimal test (no Laravel).
 * Access: https://your-domain.com/debug.php (if doc root = public/) or /public/debug.php
 * Remove after debugging.
 */
header('Content-Type: text/plain; charset=utf-8');

echo "OK - PHP works\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
echo "PHP: " . PHP_VERSION . "\n";
