<?php
// Common bootstrap for MVC

// Load environment variables first
require_once __DIR__ . '/DotEnv.php';
try {
    DotEnv::load(__DIR__ . '/../.env');
} catch (Exception $e) {
    // Log error but don't fail - fallback to defaults in config.php
    error_log('Warning: Could not load .env file: ' . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/Autoloader.php';
