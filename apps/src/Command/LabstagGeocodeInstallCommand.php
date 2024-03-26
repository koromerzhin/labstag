<?php

namespace Labstag\Command;

use Exception;
use Labstag\Lib\CommandLib;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:geocode:install')]
class LabstagGeocodeInstallCommand extends CommandLib
{
    protected function configure(): void
    {
        $this->setDescription('Récupération des géocodes');
        $this->addArgument('country', InputArgument::REQUIRED, 'code pays');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Récupération des code postaux');

        $country = $input->getArgument('country');
        if (!is_string($country)) {
            throw new Exception('Argument country invalide');
        }

        if ('' === $country) {
            $symfonyStyle->note(
                sprintf(
                    'Argument countrie obligatoire: %s',
                    $country
                )
            );

            return Command::FAILURE;
        }

        $csv = $this->geocodeService->csv($country);
        if ([] == $csv) {
            $symfonyStyle->warning(
                ['fichier inexistant']
            );

            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output, is_countable($csv) ? count($csv) : 0);
        $table       = $this->geocodeService->tables($csv);
        $progressBar->start();
        foreach ($table as $row) {
            $this->geocodeService->add($row);
            $progressBar->advance();
        }

        $progressBar->finish();
        $symfonyStyle->newLine();
        $symfonyStyle->success("Fin d'enregistrement");

        return Command::SUCCESS;
    }
}
