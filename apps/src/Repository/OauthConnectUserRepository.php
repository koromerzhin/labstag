<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\OauthConnectUser;
use Labstag\Entity\User;
use Labstag\Lib\RepositoryLib;

class OauthConnectUserRepository extends RepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, OauthConnectUser::class);
    }

    public function findDistinctAllOauth(): array
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->select('u.name');
        $queryBuilder->distinct();
        $queryBuilder->orderBy('u.name', 'ASC');

        $results = $queryBuilder->getQuery()->getResult();
        if (!is_array($results)) {
            return [];
        }

        return $results;
    }

    public function findOauthNotUser(?User $user, ?string $identity, ?string $client): ?OauthConnectUser
    {
        if (is_null($identity) || is_null($user) || is_null($client)) {
            return null;
        }

        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p.user! = :iduser');
        $queryBuilder->andWhere('p.identity = :identity');
        $queryBuilder->andWhere('p.name = :name');
        $queryBuilder->setParameters(
            [
                'iduser'   => $user->getId(),
                'name'     => $client,
                'identity' => $identity,
            ]
        );

        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        if (!$result instanceof OauthConnectUser) {
            return null;
        }

        return $result;
    }

    public function findOneOauthByUser(?string $oauthCode, ?User $user): ?OauthConnectUser
    {
        if (is_null($oauthCode) || is_null($user)) {
            return null;
        }

        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p.name = :name');
        $queryBuilder->andWhere('p.user = :iduser');
        $queryBuilder->setParameters(
            [
                'iduser' => (string) $user->getId(),
                'name'   => $oauthCode,
            ]
        );

        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        if (!$result instanceof OauthConnectUser) {
            return null;
        }

        return $result;
    }

    public function login(?string $identity, ?string $oauth): ?OauthConnectUser
    {
        if (is_null($identity) || is_null($oauth)) {
            return null;
        }

        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->where(
            'u.name = :name AND u.identity = :identity'
        );
        $queryBuilder->setParameters(
            [
                'name'     => $oauth,
                'identity' => $identity,
            ]
        );

        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        if (!$result instanceof OauthConnectUser) {
            return null;
        }

        return $result;
    }
}
