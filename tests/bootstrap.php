<?php

/**
 * PHPUnit Bootstrap File
 * 
 * This file is loaded before running tests
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load application bootstrap
require_once BASE_PATH . '/bootstrap.php';

// Set testing environment
putenv('APP_ENV=testing');

// Load test helpers
require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/Helpers/TestHelper.php';
require_once __DIR__ . '/Factories/ModelFactory.php';
