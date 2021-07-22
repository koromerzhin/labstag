<?php

namespace Labstag\Lib;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;

abstract class ServiceEntityRepositoryLib extends ServiceEntityRepository
{
    public function findAllForAdmin(): Query
    {
        $methods = get_class_methods($this);
        $name    = '';

        if (in_array('getClassMetadata', $methods)) {
            $name = $this->getClassMetadata()->getName();
        }

        $dql           = 'SELECT a FROM '.$name.' a';
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery($dql);
    }

    /**
     * Get random data.
     *
     * @return object
     */
    public function findOneRandom()
    {
        $name          = $this->getClassMetadataName();
        $dql           = 'SELECT p FROM '.$name.' p ORDER BY RAND()';
        $entityManager = $this->getEntityManager();
        $query         = $entityManager->createQuery($dql);
        $query         = $query->setMaxResults(1);
        $result        = $query->getOneOrNullResult();

        return $result;
    }

    public function findTrashForAdmin(): array
    {
        $methods = get_class_methods($this);
        $name    = '';

        if (in_array('getClassMetadata', $methods)) {
            $name = $this->getClassMetadata()->getName();
        }

        $dql = 'SELECT a FROM '.$name.' a';

        $entityManager = $this->getEntityManager();
        $dql           = $entityManager->createQueryBuilder();
        $dql->select('a');
        $dql->from($name, 'a');
        $dql->where('a.deletedAt IS NOT NULL');

        return $dql->getQuery()->getResult();
    }

    protected function getClassMetadataName(): string
    {
        $methods = get_class_methods($this);
        $name    = '';
        if (in_array('getClassMetadata', $methods)) {
            $name = $this->getClassMetadata()->getName();
        }

        return $name;
    }
}
