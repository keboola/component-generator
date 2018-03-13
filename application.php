<?php

require __DIR__ . "/vendor/autoload.php";

use Keboola\AppSkeleton\GenerateCommand;
use Symfony\Component\Console\Application;

try {
    $application = new Application();
    $command = new GenerateCommand();
    $application->add($command);
    $application->setDefaultCommand($command->getName(), true);
    $application->run();
} catch (\Throwable $e) {
    echo "An error occurred " . $e->getMessage();
}
