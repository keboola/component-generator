<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Keboola\MyComponent\Exception\UserException;
use Keboola\MyComponent\Application;

ini_set('display_errors', '1');
error_reporting(E_ALL);
set_error_handler(
    function ($errno, $errstr, $errfile, $errline, array $errcontext) {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
);

try {
    $dataDir = getenv('KBC_DATADIR') === false ? '/data/' : getenv('KBC_DATADIR');
    $configPath = $dataDir . 'config.json';
    $config = new \Keboola\MyComponent\Config($configPath);

    $app = new Application($config, $dataDir);
    $app->run();
    exit(0);
} catch (UserException $e) {
    echo $e->getMessage();
    exit(1);
} catch (Symfony\Component\Config\Definition\Exception\InvalidConfigurationException $e) {
    echo $e->getMessage();
    exit(1);
} catch(\Throwable $e) {
    echo get_class($e) . ':' . $e->getMessage();
    echo "\nFile: " . $e->getFile();
    echo "\nLine: " . $e->getLine();
    echo "\nCode: " . $e->getCode();
    echo "\nTrace: " . $e->getTraceAsString() . "\n";
    exit(2);
}
