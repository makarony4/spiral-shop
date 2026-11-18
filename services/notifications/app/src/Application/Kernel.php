<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Bootloader\AppBootloader;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Bootloader as Framework;
use Spiral\DotEnv\Bootloader\DotenvBootloader;
use Spiral\Monolog\Bootloader\MonologBootloader;
use Spiral\RoadRunnerBridge\Bootloader as RoadRunnerBridge;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;

/**
 * Мінімальний kernel: без HTTP і БД — лише черга та логування.
 * Добре видно модульність Spiral: підключаєш тільки потрібне.
 */
final class Kernel extends \Spiral\Framework\Kernel
{
    public function defineSystemBootloaders(): array
    {
        return [
            CoreBootloader::class,
            DotenvBootloader::class,
            TokenizerListenerBootloader::class,
        ];
    }

    public function defineBootloaders(): array
    {
        return [
            MonologBootloader::class,
            RoadRunnerBridge\LoggerBootloader::class,
            Framework\SnapshotsBootloader::class,

            // RoadRunner Jobs consumer
            RoadRunnerBridge\QueueBootloader::class,

            Framework\CommandBootloader::class,
            RoadRunnerBridge\CommandBootloader::class,
        ];
    }

    public function defineAppBootloaders(): array
    {
        return [
            AppBootloader::class,
        ];
    }
}
