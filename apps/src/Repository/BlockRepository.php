<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Block;
use Labstag\Entity\Page;
use Labstag\Lib\RepositoryLib;

class BlockRepository extends RepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Block::class);
    }

    public function getBlock(?Page $page): Query
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->select('c.id');
        $queryBuilder->innerjoin('c.notinpages', 'p');
        if ($page instanceof Page) {
            $queryBuilder->andWhere('p.id = :pid');
            $queryBuilder->setParameter('pid', $page->getId());
        }

        return $queryBuilder->getQuery();
    }

    /**
     * @return array<string, mixed[]>
     */
    public function getDataByRegion(?object $page): array
    {
        $regions = [
            'header',
            'content',
            'footer',
        ];
        $data       = [];
        $query      = $this->createQueryBuilder('b');
        $parameters = [];
        if (!is_null($page) && $page instanceof Page) {
            $pageQuery = $this->getBlock($page);
            $query->andWhere($query->expr()->notIn('b.id', $pageQuery->getDQL()));
            $paramsPages = $pageQuery->getParameters()->toArray();
            foreach ($paramsPages as $paramPage) {
                $parameters[$paramPage->getName()] = $paramPage->getValue();
            }
        }

        $query->andWhere(
            'b.region = :region'
        );
        $query->orderBy('b.position', 'ASC');
        foreach ($regions as $region) {
            $parameters['region'] = $region;
            $query->setParameters($parameters);
            $result        = $query->getQuery()->getResult();
            $data[$region] = $result;
        }

        return $data;
    }
}
