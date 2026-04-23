<?php
/**
 * AeroCanada v2 — Bootstrap
 * Single entry point for the v2 application.
 * Include this file at the top of any v2 page.
 */

// Prevent direct access
if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'bootstrap.php') {
    http_response_code(404);
    exit;
}

// 0. PHP version check
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('AeroCanada v2 requires PHP 7.4 or higher. Current version: ' . PHP_VERSION);
}

// 1. Output buffering
if (!ob_get_level()) {
    ob_start();
}

// 2. Load configuration
$config = require __DIR__ . '/config.php';

// 3. Timezone
date_default_timezone_set($config['app']['timezone']);

// 4. Error reporting
if ($config['app']['debug']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../../pages/_logs/php_errors.log');
}

// 5. Secure session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', $config['session']['samesite']);
    ini_set('session.gc_maxlifetime', $config['session']['lifetime']);

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }

    session_start();
}

// 6. Autoloader
require_once __DIR__ . '/autoload.php';

// 7. Legacy compatibility bridge
// This allows v2 code to coexist with v1 pages
if (!function_exists('mysql2_query')) {
    // Define $debug_mode before loading conf.php (it expects this variable)
    $debug_mode = $config['app']['debug'] ?? false;
    // Suppress errors from conf.php loading (it may overwrite handlers)
    $prevErrorHandler = set_error_handler(function() { return true; });
    $prevExceptionHandler = set_exception_handler(function() {});
    try {
        require_once __DIR__ . '/../conf.php';
    } catch (\Throwable $e) {
        // conf.php failed — not critical for v2
        error_log('[AeroCanada v2] conf.php load error: ' . $e->getMessage());
    }
    // Restore v2 handlers (will be set below anyway)
    if ($prevErrorHandler) restore_error_handler();
    if ($prevExceptionHandler) restore_exception_handler();
}

// 8. Global error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($config) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    $msg = "[$errno] $errstr in $errfile:$errline";
    error_log('[AeroCanada] ' . $msg);
    if ($config['app']['debug']) {
        echo "<pre>Error: " . htmlspecialchars($msg) . "</pre>";
    }
    return true;
});

set_exception_handler(function ($e) use ($config) {
    error_log('[AeroCanada EXCEPTION] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if ($config['app']['debug']) {
        echo "<h3>Exception</h3><pre>" . htmlspecialchars($e->getMessage()) . "\n" . $e->getTraceAsString() . "</pre>";
    } else {
        http_response_code(500);
        echo 'An error occurred. Please contact support.';
    }
});
