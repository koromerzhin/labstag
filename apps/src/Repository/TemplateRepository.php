<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\Template;
use Labstag\Lib\ServiceEntityRepositoryLib;

/**
 * @Trashable(url="admin_template_trash")
 */
class TemplateRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Template::class);
    }
}
