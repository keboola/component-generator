<?php

declare(strict_types=1);

use Keboola\MyComponent\Exception\UserException;
use Keboola\MyComponent\Application;

require __DIR__ . '/../vendor/autoload.php';

$dataDir = getenv('KBC_DATADIR') === false ? '/data/' : getenv('KBC_DATADIR');
$configPath = $dataDir . 'config.json';
$config = new \Keboola\MyComponent\Config($configPath);

try {
    $app = new Application($config, $dataDir);
    $app->run();
    exit(0);
} catch (UserException $e) {
    echo $e->getMessage();
    exit(1);
}
