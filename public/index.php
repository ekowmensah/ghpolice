<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Start session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
session_name('GHPIMS_SESSION');
session_start();

// Set timezone
date_default_timezone_set('Africa/Accra');

// Error handling
if (\App\Config\App::isDebug()) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logger("Error [{$errno}]: {$errstr} in {$errfile} on line {$errline}", 'error');
    if (\App\Config\App::isDebug()) {
        echo "<b>Error [{$errno}]:</b> {$errstr} in <b>{$errfile}</b> on line <b>{$errline}</b><br>";
    }
});

// Set exception handler
set_exception_handler(function($exception) {
    logger("Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine(), 'error');
    if (\App\Config\App::isDebug()) {
        echo "<pre>";
        echo "<b>Exception:</b> " . $exception->getMessage() . "\n";
        echo "<b>File:</b> " . $exception->getFile() . "\n";
        echo "<b>Line:</b> " . $exception->getLine() . "\n";
        echo "<b>Trace:</b>\n" . $exception->getTraceAsString();
        echo "</pre>";
    } else {
        http_response_code(500);
        echo view('errors/500');
    }
});

// Initialize router
$router = new \App\Config\Router();

// Load routes
require_once __DIR__ . '/../routes/web.php';

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Remove base path if application is in subdirectory
$basePath = '/ghpims/public';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Skip routing for static assets
$assetExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'woff', 'woff2', 'ttf', 'eot', 'ico', 'map'];
$pathInfo = pathinfo(parse_url($uri, PHP_URL_PATH));
if (isset($pathInfo['extension']) && in_array(strtolower($pathInfo['extension']), $assetExtensions)) {
    // Let the web server handle static files
    return;
}

try {
    echo $router->dispatch($method, $uri);
} catch (Exception $e) {
    logger("Routing error: " . $e->getMessage(), 'error');
    if (\App\Config\App::isDebug()) {
        throw $e;
    } else {
        http_response_code(500);
        echo view('errors/500');
    }
}
