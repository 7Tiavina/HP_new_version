<?php
/**
 * Debug file - test server/PHP without Laravel.
 * Access: https://your-domain.com/debug.php
 * Remove this file after debugging.
 */
header('Content-Type: text/plain; charset=utf-8');

echo "=== DEBUG OUTPUT ===\n\n";

echo "1. REQUEST\n";
echo "   REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '(not set)') . "\n";
echo "   REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? '(not set)') . "\n";
echo "   QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? '(empty)') . "\n\n";

echo "2. PATHS\n";
echo "   DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? '(not set)') . "\n";
echo "   SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? '(not set)') . "\n";
echo "   SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? '(not set)') . "\n\n";

echo "3. PROTOCOL / HTTPS\n";
echo "   HTTPS: " . ($_SERVER['HTTPS'] ?? '(not set)') . "\n";
echo "   SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? '(not set)') . "\n";
echo "   HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '(not set)') . "\n";
echo "   HTTP_X_FORWARDED_PORT: " . ($_SERVER['HTTP_X_FORWARDED_PORT'] ?? '(not set)') . "\n\n";

echo "4. PHP\n";
echo "   PHP_VERSION: " . PHP_VERSION . "\n";
echo "   current file exists: " . (file_exists(__FILE__) ? 'yes' : 'no') . "\n";
echo "   public/index.php exists: " . (file_exists(__DIR__ . '/public/index.php') ? 'yes' : 'no') . "\n";
echo "   public/.htaccess exists: " . (file_exists(__DIR__ . '/public/.htaccess') ? 'yes' : 'no') . "\n\n";

echo "5. PERMISSIONS\n";
echo "   This file readable: " . (is_readable(__FILE__) ? 'yes' : 'no') . "\n";
echo "   storage writable: " . (is_writable(__DIR__ . '/storage') ? 'yes' : 'no') . "\n\n";

echo "6. ENV\n";
echo "   .env exists: " . (file_exists(__DIR__ . '/.env') ? 'yes' : 'no') . "\n\n";

echo "=== END ===\n";
