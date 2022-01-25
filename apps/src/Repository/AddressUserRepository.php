<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\AddressUser;

/**
 * @Trashable(url="admin_addressuser_trash")
 */
class AddressUserRepository extends AddressRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AddressUser::class);
    }

    public function findAllForAdmin(array $get): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a');

        return $this->setQuery($queryBuilder, $get);
    }

    protected function setQuery(QueryBuilder $query, array $get): QueryBuilder
    {
        $this->setQueryCountry($query, $get);
        $this->setQueryCity($query, $get);
        $this->setQueryRefUser($query, $get);

        return $query;
    }

    protected function setQueryCity(QueryBuilder &$query, array $get)
    {
        if (!isset($get['city']) || empty($get['city'])) {
            return;
        }

        $query->andWhere('a.city LIKE :city');
        $query->setParameter('city', '%'.$get['city'].'%');
    }
}
