<?php

namespace Labstag\Command;

use Labstag\Entity\Workflow;
use Labstag\Interfaces\EntityInterface;
use Labstag\Lib\CommandLib;
use Labstag\Repository\WorkflowRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Workflow\WorkflowInterface;

#[AsCommand(name: 'labstag:workflows-show')]
class LabstagWorkflowsShowCommand extends CommandLib
{
    protected function configure(): void
    {
        $this->setDescription('Ajout des workflows en base de données');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Ajout des workflows dans la base de données');

        $data     = [];
        $entities = [];
        foreach ($this->rewindableGenerator as $entity) {
            if ($entity instanceof EntityInterface && $this->workflowService->has($entity)) {
                /** @var WorkflowInterface $workflow */
                $workflow    = $this->workflowService->get($entity);
                $definition  = $workflow->getDefinition();
                $name        = $workflow->getName();
                $entities[]  = $name;
                $transitions = $definition->getTransitions();
                foreach ($transitions as $transition) {
                    $data[$name][] = $transition->getName();
                }
            }
        }

        /** @var WorkflowRepository $repository */
        $repository = $this->repositoryService->get(Workflow::class);
        $this->delete($entities, $data);
        foreach ($data as $name => $transitions) {
            foreach ($transitions as $transition) {
                $workflow = $repository->findOneBy(
                    [
                        'entity'     => $name,
                        'transition' => $transition,
                    ]
                );
                if ($workflow instanceof Workflow) {
                    continue;
                }

                $workflow = new Workflow();
                $workflow->setEntity($name);
                $workflow->setTransition($transition);
                $repository->save($workflow);
            }
        }

        $symfonyStyle->success('Fin de traitement');

        return Command::SUCCESS;
    }

    private function delete(array $entities, array $data): void
    {
        /** @var WorkflowRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Workflow::class);
        $toDelete      = $repositoryLib->toDeleteEntities($entities);
        if (!is_iterable($toDelete)) {
            return;
        }

        foreach ($toDelete as $entity) {
            $repositoryLib->remove($entity);
        }

        foreach ($data as $entity => $transitions) {
            $toDelete = $repositoryLib->toDeleteTransition($entity, $transitions);
            if (!is_iterable($toDelete)) {
                continue;
            }

            foreach ($toDelete as $entity) {
                $repositoryLib->remove($entity);
            }
        }
    }
}
