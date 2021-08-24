<?php

declare(strict_types=1);

namespace Keboola\AppSkeleton;

use Keboola\AppSkeleton\Credentials\DeveloperPortalCredentials;
use Keboola\AppSkeleton\Credentials\DockerhubCredentials;
use Keboola\Temp\Temp;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class SetupCI
{
    public const CI_TRAVIS = 'travis';

    public const CI_GH_ACTIONS = 'github-actions';

    public static function setupGHActions(
        OutputInterface $output,
        string $repository,
        DockerhubCredentials $dockerhubCredentials,
        DeveloperPortalCredentials $developerPortalCredentials,
        string $githubToken
    ): void {
        $output->writeln('Setting up GitHub Actions integration.');

        $tmpFile = (new Temp())->createFile('token');
        file_put_contents($tmpFile->getPathname(), $githubToken);

        (new Process('gh auth login --with-token < ' . $tmpFile->getPathname()))->mustRun();

        $process = new Process(
            sprintf(
                'gh secret set KBC_DEVELOPERPORTAL_PASSWORD -b%s -R %s',
                $developerPortalCredentials->getServiceAccountPassword(),
                $repository
            )
        );
        $process->mustRun();

        if ($dockerhubCredentials->getUser() !== null) {
            $process = new Process(
                sprintf(
                    'gh secret set DOCKERHUB_TOKEN -b%s -R %s',
                    $dockerhubCredentials->getPassword(),
                    $repository
                )
            );
            $process->mustRun();
        }

        $output->writeln('Repository secret "KBC_DEVELOPERPORTAL_PASSWORD" has been created.');

        $finder = new Finder();
        $files = $finder->in('/code/.github/workflows/')->files();
        foreach ($files as $file) {
            $config = Yaml::parse(file_get_contents($file->getPathname()));
            array_walk_recursive(
                $config,
                function (&$value) use ($developerPortalCredentials, $dockerhubCredentials): void {
                    if (is_string($value) && preg_match('{{env\((?P<env>.*)\)}}', $value, $m)) {
                        $value = self::convertEnv($m['env'], $developerPortalCredentials, $dockerhubCredentials);
                    }
                }
            );
            file_put_contents($file->getPathname(), Yaml::dump($config, 10));
        }
    }

    public static function setupTravis(
        OutputInterface $output,
        string $repository,
        DeveloperPortalCredentials $developerPortalCredentials,
        string $githubToken
    ): void {
        $output->writeln('Setting up Travis integration.');

        $output->writeln('');

        $process = new Process('travis login --pro --github-token ' . escapeshellarg($githubToken));
        $process->setTty(true);
        $process->mustRun();
        ProcessDecorator::run('travis sync --pro --force', $output);
        ProcessDecorator::run('travis enable --pro --repo ' . escapeshellarg($repository), $output);
        ProcessDecorator::run('travis settings builds_only_with_travis_yml --enable --pro', $output);

        $travisEnvs = [
            [
                'env' => 'KBC_DEVELOPERPORTAL_VENDOR',
                'value' => $developerPortalCredentials->getVendorId(),
                'visibility' => 'public',
            ],
            [
                'env' => 'KBC_DEVELOPERPORTAL_APP',
                'value' => $developerPortalCredentials->getComponentId(),
                'visibility' => 'public',
            ],
            [
                'env' => 'KBC_DEVELOPERPORTAL_USERNAME',
                'value' => $developerPortalCredentials->getServiceAccountName(),
                'visibility' => 'public',
            ],
            [
                'env' => 'KBC_DEVELOPERPORTAL_PASSWORD',
                'value' => $developerPortalCredentials->getServiceAccountPassword(),
                'visibility' => 'private',
            ],
        ];

        foreach ($travisEnvs as $travisEnv) {
            $process = new Process(
                sprintf(
                    'travis env set %s %s --%s --pro',
                    $travisEnv['env'],
                    escapeshellarg($travisEnv['value']),
                    $travisEnv['visibility'],
                )
            );
            $process->mustRun();
        }

        ProcessDecorator::run('travis open --print --pro', $output);
    }

    private static function convertEnv(
        string $env,
        DeveloperPortalCredentials $developerPortalCredentials,
        DockerhubCredentials $dockerhubCredentials
    ): string {
        switch ($env) {
            case 'KBC_DEVELOPERPORTAL_APP':
                return $developerPortalCredentials->getComponentId();
            case 'KBC_DEVELOPERPORTAL_USERNAME':
                return $developerPortalCredentials->getServiceAccountName();
            case 'KBC_DEVELOPERPORTAL_VENDOR':
                return $developerPortalCredentials->getVendorId();
            case 'DOCKERHUB_USER':
                return $dockerhubCredentials->getUser();
            default:
                return $env;
        }
    }
}
