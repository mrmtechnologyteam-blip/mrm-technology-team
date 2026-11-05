<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| MRM Technology Team - Application Initialization
|--------------------------------------------------------------------------
*/

// Application bootstrap
require_once __DIR__ . '/bootstrap.php';

// Class autoloader
require_once dirname(__DIR__) . '/app/autoload.php';

// Security helpers
require_once __DIR__ . '/security.php';

// Database connection
require_once __DIR__ . '/database.php';
