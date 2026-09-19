<?php

// 1. Load Composer Autoloader
$autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// 2. Load Environment Variables via vlucas/phpdotenv
$projectRoot = dirname(__DIR__, 2);
if (file_exists($projectRoot . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
    $dotenv->safeLoad();
}

// 3. Helper Pengambilan ENV dengan Fallback
if (!function_exists('env')) {
    function env($key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

// 4. Konfigurasi Aplikasi & Domain
define('BASEURL', rtrim(env('BASEURL', 'http://localhost:8000'), '/'));
define('MAINTENANCE_MODE', filter_var(env('MAINTENANCE_MODE', false), FILTER_VALIDATE_BOOLEAN));

// 5. Database Setting (Turso libSQL Edge Database)
define('TURSO_DB_URL', rtrim(env('TURSO_DB_URL', ''), '/'));
define('TURSO_AUTH_TOKEN', env('TURSO_AUTH_TOKEN', ''));

// 6. Object Storage Setting (Backblaze B2 S3-Compatible)
define('B2_KEY_ID', env('B2_KEY_ID', ''));
define('B2_APPLICATION_KEY', env('B2_APPLICATION_KEY', ''));
define('B2_BUCKET_NAME', env('B2_BUCKET_NAME', ''));

// Normalisasi B2_REGION jika diisi hostname lengkap (misal: s3.eu-central-003.backblazeb2.com)
$rawRegion = env('B2_REGION', 'eu-central-003');
if (preg_match('/(?:s3\.)?([a-z0-9-]+)\.backblazeb2\.com/i', $rawRegion, $matches)) {
    $rawRegion = $matches[1];
}
define('B2_REGION', $rawRegion);
define('B2_ENDPOINT', env('B2_ENDPOINT', 'https://s3.' . B2_REGION . '.backblazeb2.com'));
define('B2_PUBLIC_URL', env('B2_PUBLIC_URL', ''));