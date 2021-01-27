<?php

namespace Labstag\Command;

use Labstag\Entity\Groupe;
use Labstag\Repository\GroupeRepository;
use Labstag\Repository\UserRepository;
use Labstag\Service\GuardRouteService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LabstagGuardRouteCommand extends Command
{

    protected static $defaultName = 'labstag:guard-route';

    protected GuardRouteService $service;

    protected GroupeRepository $repositoryGroupe;

    public function __construct(
        GuardRouteService $service,
        GroupeRepository $repositoryGroupe,
        string $name = null
    )
    {
        $this->repositoryGroupe = $repositoryGroupe;
        $this->service          = $service;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Enregistre les routes pour le système de GUARD');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutput = new SymfonyStyle($input, $output);
        $inputOutput->title('Installation du système de droit utilisateurs');
        $inputOutput->section('Enregistrement des routes');
        $all         = $this->service->all();
        $progressBar = new ProgressBar($output, count($all));
        $progressBar->start();
        foreach (array_keys($all) as $name) {
            $progressBar->advance();
            $this->service->save($name);
        }

        $progressBar->finish();
        $inputOutput->newLine();
        $inputOutput->success("Fin d'enregistrement");
        $table = $this->service->tables();
        $inputOutput->table(
            [
                'route',
                'controller',
            ],
            $table
        );
        $table = $this->service->old();
        if (0 != count($table)) {
            $inputOutput->section('Suppression des anciennes routes');
            $inputOutput->table(
                ['route'],
                $table
            );
            $table = $this->service->delete();
            $inputOutput->newLine();
            $inputOutput->success('Fin de suppression');
        }

        return Command::SUCCESS;
    }
}