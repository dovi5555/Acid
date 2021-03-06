<?php

declare(strict_types=1);

namespace proxy;

use Composer\Autoload\ClassLoader;
use pocketmine\utils\Config;

define("COMPOSER", "vendor/autoload.php");

$extensions = [
    "pthreads",
    "sockets",
    "yaml",
    "zlib"];

foreach ($extensions as $extension) {
    if (!extension_loaded($extension)) {
        echo "Could not start server: extension not found.";
        exit;
    }
}

if (!is_file(COMPOSER)) {
    echo "Composer autoloader not found, install composer first." . PHP_EOL;
    exit;
}

/** @var ClassLoader $loader */
/** @noinspection PhpIncludeInspection */
$loader = require COMPOSER;

$config = new Config("config.yml", Config::YAML, [
    'server-ip' => 'pe.gameteam.cz', # sorry Honzo :D
    'server-port' => 19132,
    'bind-port' => 19132
]);

$all = $config->getAll();

try {
    new ProxyServer((string)$all['server-ip'], (int)$all['server-port'], (int)$all['bind-port']);
} catch (\Exception $exception) {
    echo "Could not start server:" . $exception->getMessage();
}

