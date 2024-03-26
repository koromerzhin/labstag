<?php

namespace Labstag\Lib;

use Labstag\Service\GeocodeService;
use Labstag\Service\GuardService;
use Labstag\Service\HistoryService;
use Labstag\Service\InstallService;
use Labstag\Service\RepositoryService;
use Labstag\Service\WorkflowService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class CommandLib extends Command
{

    protected $rewindableGenerator;

    public function __construct(
        #[TaggedIterator('entitiesclass')]
        iterable $rewindableGenerator,
        protected EventDispatcherInterface $eventDispatcher,
        protected WorkflowService $workflowService,
        protected InstallService $installService,
        protected GeocodeService $geocodeService,
        protected ParameterBagInterface $parameterBag,
        protected HistoryService $historyService,
        protected GuardService $guardService,
        protected RepositoryService $repositoryService
    )
    {
        $this->rewindableGenerator = $rewindableGenerator;
        parent::__construct();
    }
}
