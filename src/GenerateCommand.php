<?php

namespace Keboola\AppSkeleton;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this->setName('skeleton:generate')
        ->setDescription('Creates a new application skeleton.')
        ->setHelp(
            'This command allows you to initialize an empty git repository with a skeleton '
            . 'of Keboola Connection application in the chosen language.'
        )
        ->addOption('setup-only', 's', InputOption::VALUE_NONE, 'Only setup deployment')
        ->addOption('update', 'u', InputOption::VALUE_NONE, 'Update skeleton');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        chdir('/code/');
        $output->writeln('<info>Hi.</info>');
        $process = (new Process('git config --get remote.origin.url'));
        $process->run();
        $url = $process->getOutput();
        if ($process->getExitCode() !== 0) {
            $output->writeln("The <info>/code/</info> directory does not seem to be a checked out git repository.");
            return;
        }
        if (!preg_match('#github\.com[:/](.*?)(?:\.git)?$#', $url, $matches)) {
            $output->writeln("The <info>/code/</info> directory does not seem to be a Github repository.");
            return;
        }
        $repository = $matches[1];
        $output->write("The current repository is: <info>" . $process->getOutput() . "</info>");
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Is this correct? ', true);
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        if (!$input->getOption('setup-only')) {
            $finder = new Finder();
            $files = $finder->files()->in('/code/')->notName('README.md');
            if (!$input->getOption('update') && $files->count()) {
                $question = new ConfirmationQuestion(
                    'The repository does not seem to be empty, do you want to continue? ',
                    true
                );
                if (!$helper->ask($input, $output, $question)) {
                    return;
                }
            }
            $dirs = [];
            $finder = new Finder();
            foreach ($finder->directories()->in('/init-code/templates/')->sortByName()->depth('== 0') as $dir) {
                /** @var SplFileInfo $dir */
                $dirs[] = $dir->getBasename(6);
            }
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion('Choose a template:', $dirs);
            $question->setErrorMessage('Template %s is invalid.');
            $template = $helper->ask($input, $output, $question);

            $output->writeln("You have wisely chosen <info>" . $template . "</info> template.");
            $fs = new Filesystem();
            $finder = new Finder();
            $output->writeln("Copying common files.");
            /** @var SplFileInfo $file */
            foreach ($finder->files()->in('/init-code/templates-common/')->ignoreDotFiles(false) as $file) {
                if ($input->getOption('update')) {
                    $question = new ConfirmationQuestion(
                        'Copy (and overwrite) file <info>' . $file->getRelativePathname() . '</info> ? ',
                        true
                    );
                    if (!$helper->ask($input, $output, $question)) {
                        continue;
                    }
                }
                $fs->copy($file->getPathname(), '/code/' . $file->getRelativePathname(), true);
            }

            /** @var SplFileInfo $directory */
            $output->writeln("Copying template files.");
            $finder = new Finder();
            foreach ($finder->files()->in('/init-code/templates/' . $template)->directories() as $directory) {
                $fs->mkdir($directory->getRelativePathname());
            }
            $finder = new Finder();
            foreach ($finder->files()->in('/init-code/templates/' . $template)->ignoreDotFiles(false) as $file) {
                if ($input->getOption('update')) {
                    $question = new ConfirmationQuestion(
                        'Copy (and overwrite) file <info>' . $file->getRelativePathname() . '</info> ? ',
                        true
                    );
                    if (!$helper->ask($input, $output, $question)) {
                        continue;
                    }
                }
                $fs->copy($file->getPathname(), '/code/' . $file->getRelativePathname(), true);
            }
            $output->writeln("Adding to git.");
            ProcessDecorator::run("git add /code/", $output);
            $output->writeln("Setting permissions.");
            ProcessDecorator::run("git update-index --chmod=+x /code/deploy.sh", $output);
            $process = new Process("git status --short");
            $process->mustRun();
            if ($process->getOutput()) {
                if (!$input->getOption('update')) {
                    $output->writeln("Creating initial commit.");
                    ProcessDecorator::run("git commit -m \"Initial import\"", $output);
                } else {
                    $output->writeln("Creating update commit.");
                    ProcessDecorator::run("git commit -m \"Skeleton updated\"", $output);
                }
            } else {
                $output->writeln("No modifications");
            }
        }

        $output->writeln("Setting up Travis integration.");
        $output->writeln("Github login");
        $process = new Process("travis login");
        $process->setTty(true);
        $process->mustRun();
        ProcessDecorator::run("travis sync", $output);
        ProcessDecorator::run("travis enable -r " . escapeshellarg($repository), $output);
        ProcessDecorator::run("travis settings builds_only_with_travis_yml --enable", $output);

        $question = new Question('Please enter <info>vendor id</info>: ');
        $vendor = $helper->ask($input, $output, $question);

        $question = new Question('Please enter <info>component id</info> (including vendor id): ');
        $componentId = $helper->ask($input, $output, $question);

        $question = new Question('Please enter service <info>account name</info>: ');
        $serviceName = $helper->ask($input, $output, $question);

        $question = new Question('Please enter service <info>account password</info>: ');
        $servicePassword = $helper->ask($input, $output, $question);
        (new Process(
            "travis env set KBC_DEVELOPERPORTAL_VENDOR " . escapeshellarg($vendor) . " --public"
        ))->mustRun();
        (new Process(
            "travis env set KBC_DEVELOPERPORTAL_APP " . escapeshellarg($componentId) . " --public"
        ))->mustRun();
        (new Process(
            "travis env set KBC_DEVELOPERPORTAL_USERNAME " . escapeshellarg($serviceName) . " --public"
        ))->mustRun();
        (new Process(
            "travis env set KBC_DEVELOPERPORTAL_PASSWORD " . escapeshellarg($servicePassword) . " --private"
        ))->mustRun();

        $output->writeln("Repository configured, adding tag to trigger deploy.");
        $process = new Process("git tag");
        $process->mustRun();
        $tags = $process->getOutput();
        if (!$tags) {
            ProcessDecorator::run("git tag 0.1.0", $output);
        } else {
            $tags = explode("\n", trim($tags));
            $tag = $tags[count($tags) - 1];
            $elms = explode('.', $tag);
            if (count($elms) == 3) {
                $elms[2]++;
                ProcessDecorator::run("git tag " . implode(".", $elms), $output);
            } else {
                $output->writeln("Don't know how to tag based on <info>$tag</info>, create a new git tag.");
            }
        }

        ProcessDecorator::run("travis open --print", $output);
        $output->writeln("Verify what I have done and do <info>git push</info> to deploy the application "
            . "or <info>git reset --hard origin/master</info> to rollback all changes.");
    }
}
