<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\Page;
use Labstag\Lib\RepositoryLib;

#[Trashable(url: 'admin_page_trash')]
class PageRepository extends RepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Page::class);
    }

    public function formType(array $options): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $id           = $options['data']->getId();
        if (empty($id)) {
            return $queryBuilder;
        }

        $queryBuilder->where(
            'p.id != :id'
        );
        $queryBuilder->setParameters(
            ['id' => $id]
        );

        return $queryBuilder;
    }

    public function getBySlugs(array $slugs): mixed
    {
        $queryBuilder = $this->createQueryBuilder('p');

        $queryBuilder->where(
            'p.slug IN (:slugs)'
        );
        $queryBuilder->setParameters(
            ['slugs' => $slugs]
        );

        return $queryBuilder->getQuery()->getResult();
    }
}
