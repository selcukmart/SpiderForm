<?php

declare(strict_types=1);

/*
 * PHPUnit Bootstrap File for FormGenerator V2
 * 
 * This file is automatically loaded before running tests.
 */

// Load Composer autoloader
$autoloader = require __DIR__ . '/../vendor/autoload.php';

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('UTC');

// Define test constants
define('FORMGEN_TEST_MODE', true);
define('FORMGEN_TEST_DIR', __DIR__);
define('FORMGEN_ROOT_DIR', dirname(__DIR__));

// Register test helpers if needed
if (file_exists(__DIR__ . '/TestHelpers.php')) {
    require_once __DIR__ . '/TestHelpers.php';
}

// Ensure coverage directory exists
$coverageDir = FORMGEN_ROOT_DIR . '/coverage';
if (!is_dir($coverageDir)) {
    mkdir($coverageDir, 0755, true);
}

// Output test environment info
if (getenv('VERBOSE_TESTS') === '1') {
    echo PHP_EOL;
    echo "FormGenerator V2 Test Suite" . PHP_EOL;
    echo "============================" . PHP_EOL;
    echo "PHP Version: " . PHP_VERSION . PHP_EOL;
    echo "PHPUnit: " . PHPUnit\Runner\Version::id() . PHP_EOL;
    echo "Test Directory: " . FORMGEN_TEST_DIR . PHP_EOL;
    echo "Root Directory: " . FORMGEN_ROOT_DIR . PHP_EOL;
    echo PHP_EOL;
}
