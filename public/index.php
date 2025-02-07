<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Security;
use App\Core\Logger;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize error handling
ini_set('display_errors', $_ENV['APP_DEBUG'] === 'true' ? '1' : '0');
error_reporting(E_ALL);

// Initialize logger
$logger = new Logger();

// Set up secure session
Security::setupSecureSession();

// Initialize router
$router = new Router();

// Register routes
require_once __DIR__ . '/../app/routes.php';

try {
    // Dispatch the request
    echo $router->dispatch();
} catch (Exception $e) {
    $logger->error($e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    if ($_ENV['APP_DEBUG'] === 'true') {
        throw $e;
    } else {
        http_response_code(500);
        echo 'An error occurred. Please try again later.';
    }
}