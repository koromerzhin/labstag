<?php

namespace Labstag\Lib;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;

abstract class ServiceEntityRepositoryLib extends ServiceEntityRepository
{

    protected function getClassMetadataName(): string
    {
        $methods = get_class_methods($this);
        $name    = '';
        if (array_key_exists('getClassMetadata', $methods)) {
            $name = $this->getClassMetadata()->getName();
        }

        return $name;
    }

    /**
     * Get random data.
     *
     * @return object
     */
    public function findOneRandom()
    {
        $name          = $this->getClassMetadataName();
        $dql           = 'SELECT p FROM ' . $name . ' p ORDER BY RAND()';
        $entityManager = $this->getEntityManager();
        $query         = $entityManager->createQuery($dql);
        $query         = $query->setMaxResults(1);
        $result        = $query->getOneOrNullResult();

        return $result;
    }

    public function findAllForAdmin(): Query
    {
        $methods = get_class_methods($this);
        $name    = '';
        if (array_key_exists('getClassMetadata', $methods)) {
            $name = $this->getClassMetadata()->getName();
        }

        $dql           = 'SELECT a FROM ' . $name . ' a';
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery($dql);
    }
}
