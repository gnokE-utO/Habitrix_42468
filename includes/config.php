<?php
// Database configuration
define('DB_PATH', __DIR__ . '\..\database/habitrix.db');
define('APP_NAME', 'HabitriX');
define('APP_VERSION', '1.0.0');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Start session
session_start();
?>
