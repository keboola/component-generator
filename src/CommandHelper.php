<?php

declare(strict_types=1);

namespace Keboola\AppSkeleton;

use Keboola\AppSkeleton\Credentials\DeveloperPortalCredentials;
use Keboola\AppSkeleton\Credentials\DockerhubCredentials;
use Keboola\AppSkeleton\Exception\FailedException;
use RuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

class CommandHelper
{
    private InputInterface $input;

    private OutputInterface $output;

    private QuestionHelper $questionHelper;

    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $questionHelper;
    }

    public function getGithubToken(): string
    {
        $this->output->writeln('Github login');
        // Use CLI option if present
        $cliToken = $this->input->getOption('github-token');
        if ($cliToken) {
            $this->output->writeln('Using GitHub API token from the command line.');
            return $cliToken;
        }

        // Request token from user
        $this->output->writeln('Please provide a GitHub token from the https://github.com/settings/tokens');
        $this->output->writeln('For required scopes, see https://docs.travis-ci.com/user/github-oauth-scopes');

        $question = new Question('GitHub token:');
        $this->setQuestionValidator($question, 'GitHub token');
        $githubToken = $this->questionHelper->ask($this->input, $this->output, $question);

        // Remove previous line and replace token with *****
        if ($this->output->isDecorated()) {
            $this->output->write("\x1B[1A\x1B[2K");
            $this->output->writeln('GitHub token: *****');
        }

        return $githubToken;
    }

    public function getDeveloperPortalCredentials(): DeveloperPortalCredentials
    {
        $this->output->writeln('Developer Portal credentials');

        $question = new Question('Please enter <info>vendor id</info>: ');
        $this->setQuestionValidator($question, 'vendor id');
        $vendor = $this->questionHelper->ask($this->input, $this->output, $question);

        $question = new Question(
            'Please enter <info>component id</info> (including vendor id, e.g. keboola.ex-gmail): '
        );
        $this->setQuestionValidator($question, 'component id');

        $componentId = $this->questionHelper->ask($this->input, $this->output, $question);

        $question = new Question('Please enter service <info>account name</info>: ');
        $this->setQuestionValidator($question, 'account name');
        $serviceName = $this->questionHelper->ask($this->input, $this->output, $question);

        $question = new Question('Please enter service <info>account password</info>: ');
        $this->setQuestionValidator($question, 'account password');
        $servicePassword = $this->questionHelper->ask($this->input, $this->output, $question);

        // Remove previous line and replace password with *****
        if ($this->output->isDecorated()) {
            $this->output->write("\x1B[1A\x1B[2K");
            $this->output->writeln('Please enter service <info>account password</info>:  *****');
        }

        return new DeveloperPortalCredentials($vendor, $componentId, $serviceName, $servicePassword);
    }

    public function getDockerhubCredentials(): DockerhubCredentials
    {
        $this->output->writeln('Dockerhub credentials');

        $question = new Question('Please enter <info>Dockerhub username</info> (keep empty for skip): ');
        $user = $this->questionHelper->ask($this->input, $this->output, $question);

        $password = null;
        if ($user) {
            $question = new Question('Please enter <info>Dockerhub password</info>: ');
            $question->setValidator(fn($v) => !empty($v));
            $password = $this->questionHelper->ask($this->input, $this->output, $question);

            // Remove previous line and replace password with *****
            if ($this->output->isDecorated()) {
                $this->output->write("\x1B[1A\x1B[2K");
                $this->output->writeln('Please enter <info>Dockerhub password</info>: *****');
            }
        }

        return new DockerhubCredentials($user, $password);
    }

    public function getRepository(): string
    {
        $process = (new Process('git config --get remote.origin.url'));
        $process->run();
        $url = $process->getOutput();
        if ($process->getExitCode() !== 0) {
            throw new FailedException(
                'The <info>/code/</info> directory does not seem to be a checked out git repository.'
            );
        }
        if (!preg_match('#github\.com[:/](.*?)(?:\.git)?$#', $url, $matches)) {
            throw new FailedException(
                'The <info>/code/</info> directory does not seem to be a Github repository.'
            );
        }

        $this->output->write('The current repository is: <info>' . $process->getOutput() . '</info>');
        $question = new ConfirmationQuestion('Is this correct? ', true);
        if (!$this->questionHelper->ask($this->input, $this->output, $question)) {
            throw new FailedException();
        }
        return $matches[1];
    }

    public function checkRepositoryIfEmpty(): void
    {
        $finder = new Finder();
        $files = $finder->files()->in('/code/')->notName('README.md');
        if (!$this->input->getOption('update') && $files->count()) {
            $question = new ConfirmationQuestion(
                'The repository does not seem to be empty, do you want to continue? ',
                true
            );
            if (!$this->questionHelper->ask($this->input, $this->output, $question)) {
                throw new FailedException();
            }
        }
    }

    public function chooseTemplate(): string
    {
        $dirs = [];
        $finder = new Finder();
        foreach ($finder->directories()->in('/init-code/templates/')->sortByName()->depth(0) as $dir) {
            /** @var SplFileInfo $dir */
            $dirs[] = $dir->getBasename();
        }
        $question = new ChoiceQuestion('Choose a template:', $dirs);
        $question->setErrorMessage('Template %s is invalid.');

        $template = $this->questionHelper->ask($this->input, $this->output, $question);

        $this->output->writeln('You have wisely chosen <info>' . $template . '</info> template.');

        return $template;
    }

    public function chooseCiTemplate(string $template): string
    {
        $dirs = [];
        $finder = new Finder();
        $templateDir = '/init-code/templates-ci/' . $template;
        if (!is_dir($templateDir)) {
            $templateDir = '/init-code/templates-ci/common/';
        }
        foreach ($finder->directories()->in($templateDir)->sortByName()->depth(0) as $dir) {
            /** @var SplFileInfo $dir */
            $dirs[] = $dir->getBasename();
        }

        if (count($dirs) === 1) {
            return (string) current($dirs);
        }

        $question = new ChoiceQuestion('Choose a template:', $dirs);
        $question->setErrorMessage('Template %s is invalid.');

        $template = $this->questionHelper->ask($this->input, $this->output, $question);

        $this->output->writeln('You have wisely chosen <info>' . $template . '</info> CI template.');

        return $template;
    }

    public function copyTemplateFiles(string $template): void
    {
        $this->output->writeln('Copying common files.');
        $this->copyFiles('/init-code/templates-common/');

        $this->output->writeln('Copying template files.');
        $this->copyFiles('/init-code/templates/' . $template);
    }

    public function copyFiles(string $sourceDir): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        /** @var SplFileInfo $directory */
        foreach ($finder->files()->in($sourceDir)->directories() as $directory) {
            $fs->mkdir($directory->getRelativePathname());
        }
        $finder = new Finder();
        foreach ($finder->files()->in($sourceDir)->ignoreDotFiles(false) as $file) {
            if ($this->input->getOption('update')) {
                $question = new ConfirmationQuestion(
                    'Copy (and overwrite) file <info>' . $file->getRelativePathname() . '</info> ? ',
                    true
                );
                if (!$this->questionHelper->ask($this->input, $this->output, $question)) {
                    continue;
                }
            }
            $fs->copy($file->getPathname(), '/code/' . $file->getRelativePathname(), true);
        }
    }

    public function createGitCommit(): void
    {
        $this->output->writeln('Adding to git.');
        ProcessDecorator::run('git add /code/', $this->output);
        if (file_exists('/code/deploy.sh')) {
            $this->output->writeln('Setting permissions.');
            ProcessDecorator::run('git update-index --chmod=+x /code/deploy.sh', $this->output);
        }
        $process = new Process('git status --short');
        $process->mustRun();
        if ($process->getOutput()) {
            if (!$this->input->getOption('update')) {
                $this->output->writeln('Creating initial commit.');
                ProcessDecorator::run('git commit -m "Initial import"', $this->output);
            } else {
                $this->output->writeln('Creating update commit.');
                ProcessDecorator::run('git commit -m "Skeleton updated"', $this->output);
            }
        } else {
            $this->output->writeln('No modifications');
        }
    }

    public function createGitTag(): void
    {
        $this->output->writeln('Repository configured, adding tag to trigger deploy.');
        $process = new Process('git tag');
        $process->mustRun();
        $tags = $process->getOutput();
        if (!$tags) {
            ProcessDecorator::run('git tag 0.1.0', $this->output);
        } else {
            $tags = explode("\n", trim($tags));
            $tag = $tags[count($tags) - 1];
            $elms = explode('.', $tag);
            if (count($elms) === 3) {
                $elms[2]++;
                ProcessDecorator::run('git tag ' . implode('.', $elms), $this->output);
            } else {
                $this->output->writeln('Don\'t know how to tag based on <info>$tag</info>, create a new git tag.');
            }
        }
    }

    private function setQuestionValidator(Question $question, string $type): void
    {
        $question->setValidator(function ($v) use ($type) {
            if (empty($v)) {
                throw new RuntimeException(sprintf('Please fill in "%s".', $type));
            }

            return $v;
        });
    }
}
