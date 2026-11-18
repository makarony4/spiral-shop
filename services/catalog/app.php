<?php

declare(strict_types=1);

use App\Application\Kernel;

\mb_internal_encoding('UTF-8');
\error_reporting(E_ALL ^ E_DEPRECATED);
\ini_set('display_errors', 'stderr');

require __DIR__ . '/vendor/autoload.php';

// Єдина точка входу: і для HTTP-воркерів RoadRunner, і для консолі (php app.php ...).
// Kernel сам обирає Dispatcher залежно від RR_MODE (http / jobs / cli).
$app = Kernel::create(directories: ['root' => __DIR__])->run();

if ($app === null) {
    exit(255);
}

exit((int) $app->serve());
