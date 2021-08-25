<?php

declare(strict_types=1);

namespace Keboola\AppSkeleton;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ProcessDecorator
{
    public static function run(string $commandLine, OutputInterface $output): void
    {
        $output->writeln('Executing command: <info>' . $commandLine . '</info>');
        $process = new Process($commandLine);
        $process->mustRun();
        $output->writeln($process->getOutput());
    }
}
