<?php
declare(strict_types=1);

use App\Core\App;
use App\Core\Database;

// Autoloader must already be required by the caller (index.php).
// But we defensively re-check in case bootstrap.php is used from CLI too.
if (!class_exists(App::class, false)) {
    require __DIR__ . '/../vendor/autoload.php';
}

// --- Config first (this is what loads .env via Dotenv) ---
$config = require __DIR__ . '/../config/config.php';
App::init($config);

// --- Errors (needs config loaded to know debug flag) ---
$debug = (bool) config('app.debug', false);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

// --- Database ---
Database::init();

// --- Placeholder: prove the pipeline works ---
echo "<h1>Bootstrap works</h1>";
echo "<p>Environment: " . e(config('app.env')) . "</p>";
echo "<p>Debug: " . var_export(config('app.debug'), true) . "</p>";
echo "<p>DB: " . e(config('db.name')) . " @ " . e(config('db.host')) . "</p>";
echo "<p>Escape test: " . e('<b>x</b>') . "</p>";