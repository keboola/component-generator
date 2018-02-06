<?php

declare(strict_types=1);

namespace Keboola\MyComponent;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class Application
{
    /** @var Config */
    private $config;

    /** @var string */
    private $dataDir;

    /** @var Logger */
    private $logger;

    public function __construct(Config $config, string $dataDir)
    {
        $this->config = $config;
        $this->dataDir = $dataDir;
        $formatter = new LineFormatter("%message%\n");
        $errHandler = new StreamHandler('php://stderr', Logger::NOTICE, false);
        $errHandler->setFormatter($formatter);
        $handler = new StreamHandler('php://stdout', Logger::INFO);
        $handler->setFormatter($formatter);
        $this->logger = new Logger('main', [$errHandler, $handler]);
    }

    public function run(): void
    {
        $this->logger->info("Hello world from Symfony");
    }
}
