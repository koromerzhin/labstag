<?php

namespace Labstag\Repository\Paragraph\Post;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Paragraph\Post\Libelle;
use Labstag\Lib\ServiceEntityRepositoryLib;

class LibelleRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Libelle::class);
    }
}
