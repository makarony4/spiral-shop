<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Bootloader\AppBootloader;
use App\Application\Bootloader\RoutesBootloader;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Bootloader as Framework;
use Spiral\Cycle\Bootloader as CycleBridge;
use Spiral\DotEnv\Bootloader\DotenvBootloader;
use Spiral\Monolog\Bootloader\MonologBootloader;
use Spiral\Nyholm\Bootloader\NyholmBootloader;
use Spiral\RoadRunnerBridge\Bootloader as RoadRunnerBridge;
use Spiral\Router\Bootloader\AnnotatedRoutesBootloader;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Spiral\Validation\Bootloader\ValidationBootloader;
use Spiral\Validator\Bootloader\ValidatorBootloader;

/**
 * Kernel — аналог bootstrap/app.php (Laravel) чи Kernel::registerBundles() (Symfony).
 * Застосунок складається з bootloader-ів: кожен підключає свій шматок функціональності.
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
            // Logging (Monolog -> RoadRunner logger)
            MonologBootloader::class,
            RoadRunnerBridge\LoggerBootloader::class,

            // Core
            Framework\SnapshotsBootloader::class,
            Framework\Attributes\AttributesBootloader::class,

            // HTTP через RoadRunner
            NyholmBootloader::class,
            RoadRunnerBridge\HttpBootloader::class,
            Framework\Http\RouterBootloader::class,
            Framework\Http\JsonPayloadsBootloader::class,
            Framework\Http\ErrorHandlerBootloader::class,
            AnnotatedRoutesBootloader::class,

            // gRPC-сервер через RoadRunner (той самий процес, окремий воркер-пул)
            RoadRunnerBridge\GRPCBootloader::class,

            // Валідація вхідних даних (Request Filters)
            ValidationBootloader::class,
            ValidatorBootloader::class,
            Framework\Security\FiltersBootloader::class,

            // Database + Cycle ORM + міграції
            CycleBridge\DatabaseBootloader::class,
            CycleBridge\MigrationsBootloader::class,
            CycleBridge\SchemaBootloader::class,
            CycleBridge\CycleOrmBootloader::class,
            CycleBridge\AnnotatedBootloader::class,
            CycleBridge\CommandBootloader::class,

            // Console
            Framework\CommandBootloader::class,
            RoadRunnerBridge\CommandBootloader::class,
        ];
    }

    public function defineAppBootloaders(): array
    {
        return [
            RoutesBootloader::class,
            AppBootloader::class,
        ];
    }
}
