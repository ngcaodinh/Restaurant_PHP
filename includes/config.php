<?php
// Load environment variables
require_once __DIR__ . '/../core/DotEnv.php';

// Define constants from environment variables with fallback defaults
define('BASE_URL', DotEnv::get('BASE_URL', 'http://localhost/Restaurant_PHP/'));
define('DB_HOST', DotEnv::get('DB_HOST', 'localhost'));
define('DB_USER', DotEnv::get('DB_USER', 'root'));
define('DB_PASS', DotEnv::get('DB_PASS', ''));
define('DB_NAME', DotEnv::get('DB_NAME', 'Restaurant_CTUT'));
define('GOOGLE_CLIENT_ID', DotEnv::get('GOOGLE_CLIENT_ID', ''));
define('GOOGLE_CLIENT_SECRET', DotEnv::get('GOOGLE_CLIENT_SECRET', ''));
define('GOOGLE_REDIRECT_URI', DotEnv::get('GOOGLE_REDIRECT_URI', 'http://localhost/Restaurant_PHP/google_callback.php'));
