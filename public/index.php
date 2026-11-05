<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| MRM Technology Team
| Application Front Controller
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__) . '/config/init.php';

use App\Http\Kernel;
use App\Http\Router;

/*
|--------------------------------------------------------------------------
| Load Web Routes
|--------------------------------------------------------------------------
*/

$router = require BASE_PATH . '/routes/web.php';

if (!$router instanceof Router) {
    throw new RuntimeException(
        'Web routes configuration must return a Router instance.'
    );
}

/*
|--------------------------------------------------------------------------
| Start Application Kernel
|--------------------------------------------------------------------------
*/

$kernel = new Kernel($router);

$kernel->run();
