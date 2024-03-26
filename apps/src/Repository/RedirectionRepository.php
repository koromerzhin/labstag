<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Redirection;
use Labstag\Lib\RepositoryLib;

class RedirectionRepository extends RepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Redirection::class);
    }
}
