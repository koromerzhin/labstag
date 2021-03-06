<?php

namespace Labstag\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LabstagUpdateCommand extends Command
{

    protected static $defaultName = 'labstag:update';

    protected function configure()
    {
        $this->setDescription('Add a short description for your command');
        $this->addOption('maintenanceon', null, InputOption::VALUE_NONE, 'Enable maintenance');
        $this->addOption('maintenanceoff', null, InputOption::VALUE_NONE, 'Disable maintenance');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutput = new SymfonyStyle($input, $output);
        if ($input->getOption('maintenanceon')) {
            $maintanceFile = file_get_contents('maintenance.html');
            file_put_contents('public/index.php', $maintanceFile);
            $inputOutput->note('Maintenance activé');
        }

        if ($input->getOption('maintenanceoff')) {
            $publicFile = file_get_contents('public.php');
            file_put_contents('public/index.php', $publicFile);
            $inputOutput->note('Maintenance désactivé');
        }

        $inputOutput->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
