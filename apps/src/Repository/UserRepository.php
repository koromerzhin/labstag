<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\User;
use Labstag\Lib\RepositoryLib;

#[Trashable(url: 'admin_user_trash')]
class UserRepository extends RepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, User::class);
    }

    public function findOauth(
        string $identity,
        string $name
    ): mixed
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->leftJoin('u.oauthConnectUsers', 'o');
        $queryBuilder->where('o.name = :name');
        $queryBuilder->andWhere('o.identity=:identity');
        $queryBuilder->setParameters(
            [
                'name'     => $name,
                'identity' => $identity,
            ]
        );

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findUserEnable(string $field): ?User
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->where(
            'u.username=:username OR u.email=:email'
        );
        $queryBuilder->andWhere('u.state LIKE :state1 OR u.state LIKE :state2');
        $queryBuilder->setParameters(
            [
                'state1'   => '%valider%',
                'state2'   => '%lostpassword%',
                'username' => $field,
                'email'    => $field,
            ]
        );

        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        if (!$result instanceof User) {
            return null;
        }

        return $result;
    }

    public function findUserName(string $field): mixed
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->where(
            'u.username LIKE :username OR u.email LIKE :email'
        );
        $queryBuilder->setParameters(
            [
                'username' => '%'.$field.'%',
                'email'    => '%'.$field.'%',
            ]
        );

        return $queryBuilder->getQuery()->getResult();
    }
}
