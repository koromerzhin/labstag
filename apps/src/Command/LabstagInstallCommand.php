<?php

namespace Labstag\Command;

use Labstag\Service\InstallService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LabstagInstallCommand extends Command
{

    protected static $defaultName = 'labstag:install';

    protected InstallService $installService;

    public function __construct(
        InstallService $installService
    )
    {
        $this->installService = $installService;
        parent::__construct();
    }

    protected function all($inputOutput)
    {
        $inputOutput->note('Installations');
        $this->setMenuAdmin($inputOutput);
        $this->setMenuAdminProfil($inputOutput);
        $this->setGroup($inputOutput);
        $this->setConfig($inputOutput);
        $this->setTemplates($inputOutput);
        $this->setUsers($inputOutput);
    }

    protected function configure()
    {
        $this->setDescription('Add a short description for your command');
        $this->addOption('menuadmin', null, InputOption::VALUE_NONE, 'menuadmin');
        $this->addOption('menuadminprofil', null, InputOption::VALUE_NONE, 'menuadminprofil');
        $this->addOption('group', null, InputOption::VALUE_NONE, 'group');
        $this->addOption('config', null, InputOption::VALUE_NONE, 'config');
        $this->addOption('templates', null, InputOption::VALUE_NONE, 'templates');
        $this->addOption('users', null, InputOption::VALUE_NONE, 'users');
        $this->addOption('all', null, InputOption::VALUE_NONE, 'all');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutput = new SymfonyStyle($input, $output);
        if ($input->getOption('menuadmin')) {
            $this->setMenuAdmin($inputOutput);
        } elseif ($input->getOption('menuadminprofil')) {
            $this->setMenuAdminProfil($inputOutput);
        } elseif ($input->getOption('group')) {
            $this->setGroup($inputOutput);
        } elseif ($input->getOption('config')) {
            $this->setConfig($inputOutput);
        } elseif ($input->getOption('templates')) {
            $this->setTemplates($inputOutput);
        } elseif ($input->getOption('users')) {
            $this->setUsers($inputOutput);
        } elseif ($input->getOption('all')) {
            $this->all($inputOutput);
        }

        $inputOutput->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    protected function setConfig($inputOutput)
    {
        $inputOutput->note('Ajout de la configuration');
        $this->installService->config();
    }

    protected function setGroup($inputOutput)
    {
        $inputOutput->note('Ajout des groupes');
        $this->installService->group();
    }

    protected function setMenuAdmin($inputOutput)
    {
        $inputOutput->note('Ajout du menu admin');
        $this->installService->menuadmin();
    }

    protected function setMenuAdminProfil($inputOutput)
    {
        $inputOutput->note('Ajout du menu admin profil');
        $this->installService->menuadminprofil();
    }

    protected function setTemplates($inputOutput)
    {
        $inputOutput->note('Ajout des templates');
        $this->installService->templates();
    }

    protected function setUsers($inputOutput)
    {
        $inputOutput->note('Ajout des users');
        $this->installService->users();
    }
}
