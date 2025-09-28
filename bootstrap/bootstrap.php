<?php

/*
 * Bootstrap file for GitHub Analyzer
 */

// Define application paths
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');

// Simple autoloader for our classes
spl_autoload_register(function ($class) {
    // Convert namespace to path
    $path = str_replace(['\\', 'App/'], ['/', APP_PATH . '/'], $class) . '.php';
    
    if (file_exists($path)) {
        require_once $path;
        return true;
    }
    
    return false;
});

// Load environment variables if .env exists
if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"\'');
        }
    }
}