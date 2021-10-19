<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\Libelle;
use Labstag\Lib\ServiceEntityRepositoryLib;

/**
 * @Trashable(url="admin_libelle_trash")
 */
class LibelleRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Libelle::class);
    }

    public function findByBookmark()
    {
        $entityManager = $this->getEntityManager();
        $dql           = $entityManager->createQueryBuilder();
        $dql->select('a');
        $dql->from(Libelle::class, 'a');
        $dql->innerJoin('a.bookmarks', 'b');
        $dql->innerjoin('b.refuser', 'u');
        $dql->where('b.state LIKE :state');
        $dql->setParameters(
            ['state' => '%publie%']
        );

        return $dql->getQuery()->getResult();
    }

    public function findByPost()
    {
        $entityManager = $this->getEntityManager();
        $dql           = $entityManager->createQueryBuilder();
        $dql->select('a');
        $dql->from(Libelle::class, 'a');
        $dql->innerJoin('a.posts', 'p');
        $dql->innerjoin('p.refuser', 'u');
        $dql->where('p.state LIKE :state');
        $dql->setParameters(
            ['state' => '%publie%']
        );

        return $dql->getQuery()->getResult();
    }

    public function findNom(string $field)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $query        = $queryBuilder->where(
            'u.nom LIKE :nom'
        );
        $query->setParameters(
            [
                'nom' => '%'.$field.'%',
            ]
        );

        return $query->getQuery()->getResult();
    }

    protected function setQuery(QueryBuilder $query, array $get): QueryBuilder
    {
        $this->setQueryNom($query, $get);

        return $query;
    }

    protected function setQueryNom(QueryBuilder &$query, array $get)
    {
        if (!isset($get['nom']) || empty($get['nom'])) {
            return;
        }

        $query->andWhere('a.nom LIKE :nom');
        $query->setParameter('nom', '%'.$get['nom'].'%');
    }
}
