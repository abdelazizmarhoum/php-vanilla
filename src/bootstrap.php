<?php
declare(strict_types=1);

use App\Core\App;
use App\Core\Session;
use App\Core\Database;
use App\Core\Router;

// Autoloader must already be required by the caller (index.php).
// But we defensively re-check in case bootstrap.php is used from CLI too.
if (!class_exists(App::class, false)) {
    require __DIR__ . '/../vendor/autoload.php';
}

// --- Config first (this is what loads .env via Dotenv) ---
$config = require __DIR__ . '/../config/config.php';
App::init($config);
Session::start();

// --- Errors (needs config loaded to know debug flag) ---
$debug = (bool) config('app.debug', false);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

// --- Database ---
Database::init();

// --- Router ---
$router = new Router();
$routes = require __DIR__ . '/routes.php';
$routes($router);

$method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = (string) ($_SERVER['REQUEST_URI'] ?? '/');

$router->dispatch($method, $path);