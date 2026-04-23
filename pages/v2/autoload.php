<?php
/**
 * AeroCanada v2 — PSR-4 Autoloader
 * Maps AeroCanada\Core\* to v2/core/
 * Maps AeroCanada\Modules\* to v2/modules/
 */

spl_autoload_register(function (string $class) {
    $prefix = 'AeroCanada\\';
    $baseDir = __DIR__ . '/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // AeroCanada\Core\Database → v2/Core/Database.php
    // But our dirs are lowercase, so map:
    // Core → core, Modules → modules
    $file = str_replace('/Core/', '/core/', $file);
    $file = str_replace('/Modules/', '/modules/', $file);

    if (file_exists($file)) {
        require $file;
    }
});
