<?php

declare(strict_types=1);

namespace Keboola\AppSkeleton;

use Keboola\AppSkeleton\Exception\FailedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('skeleton:generate')
        ->setDescription('Creates a new application skeleton.')
        ->setHelp(
            'This command allows you to initialize an empty git repository with a skeleton '
            . 'of Keboola Connection application in the chosen language.'
        )
        ->addOption('setup-only', 's', InputOption::VALUE_NONE, 'Only setup deployment')
        ->addOption('update', 'u', InputOption::VALUE_NONE, 'Update skeleton')
        ->addOption('github-token', 't', InputOption::VALUE_OPTIONAL, 'GitHub API token');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $commandHelper = new CommandHelper(
            $input,
            $output,
            $this->getHelper('question')
        );
        try {
            chdir('/code/');
            $output->writeln('<info>Hi.</info>');

            $repository = $commandHelper->getRepository();

            $template = 'common';
            if (!$input->getOption('setup-only')) {
                $commandHelper->checkRepositoryIfEmpty();

                $template = $commandHelper->chooseTemplate();

                $commandHelper->copyTemplateFiles($template);
            }

            $ciTemplate = $commandHelper->chooseCiTemplate($template);
            $output->writeln('Copying CI template files.');
            $ciTemplateDir = '/init-code/templates-ci/' . $template . '/'. $ciTemplate;
            if (!is_dir($ciTemplateDir)) {
                $ciTemplateDir = '/init-code/templates-ci/common/' . $ciTemplate;
            }
            $commandHelper->copyFiles($ciTemplateDir);

            $developerPortalCredentials = $commandHelper->getDeveloperPortalCredentials();

            $githubToken = $commandHelper->getGithubToken();

            switch ($ciTemplate) {
                case SetupCI::CI_GH_ACTIONS:
                    $dockerhubCredentials = $commandHelper->getDockerhubCredentials();
                    SetupCI::setupGHActions(
                        $output,
                        $repository,
                        $dockerhubCredentials,
                        $developerPortalCredentials,
                        $githubToken
                    );
                    break;
            }

            $commandHelper->createGitCommit();
        } catch (FailedException $e) {
            $output->writeln($e->getMessage());
            return;
        }

        $commandHelper->createGitTag();

        $output->writeln(
            'Verify what I have done and do <info>git push</info> to deploy the application '
            . 'or <info>git reset --hard origin/master</info> to rollback all changes.'
        );
    }
}
