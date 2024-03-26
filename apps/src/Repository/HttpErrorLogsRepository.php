<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\HttpErrorLogs;
use Labstag\Lib\RepositoryLib;

class HttpErrorLogsRepository extends RepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HttpErrorLogs::class);
    }
}
