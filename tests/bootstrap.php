<?php

declare(strict_types=0);

require_once __DIR__ . '/Stubs/BgaFrameworkStubs.php';
require_once __DIR__ . '/../states.inc.php';
require_once __DIR__ . '/../gameoptions.inc.php';
require_once __DIR__ . '/../diceforge.action.php';

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'Bga\\Games\\DiceForge\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $path = __DIR__ . '/../modules/php/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($path)) {
        require_once $path;
    }
});

spl_autoload_register(static function (string $class): void {
    $prefix  = 'Bga\\Games\\diceforge\\';
    $exclude = 'Bga\\Games\\diceforge\\Tests\\';

    if (!str_starts_with($class, $prefix) || str_starts_with($class, $exclude)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $segments      = explode('\\', $relativeClass);
    $fileName      = array_pop($segments) . '.php';
    $base          = __DIR__ . '/../modules/php/';

    // Try exact-case path first, then lowercase on directory segments
    // (namespace uses 'Db' but the folder on disk is 'db').
    $dirCandidates = array_unique([
        implode('/', $segments),
        implode('/', array_map('strtolower', $segments)),
    ]);

    foreach ($dirCandidates as $dir) {
        $path = $base . ($dir !== '' ? $dir . '/' : '') . $fileName;
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

spl_autoload_register(static function (string $class): void {
    $prefix = 'Bga\\Games\\diceforge\\Tests\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', '/', $relativeClass) . '.php';

    foreach ([__DIR__, __DIR__ . '/Support', __DIR__ . '/Game'] as $base) {
        $path = $base . '/' . $relativePath;
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
