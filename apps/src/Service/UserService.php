<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\OauthConnectUser;
use Labstag\Entity\User;
use Labstag\Repository\OauthConnectUserRepository;
use Labstag\Repository\UserRepository;
use Labstag\RequestHandler\OauthConnectUserRequestHandler;
use Labstag\RequestHandler\UserRequestHandler;
use League\OAuth2\Client\Provider\AbstractProvider;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserService
{

    protected EntityManagerInterface $entityManager;

    protected OauthConnectUserRequestHandler $oauthConnectUserRH;

    protected OauthService $oauthService;

    protected UserRepository $repository;

    protected SessionInterface $session;

    protected UserRequestHandler $userRH;

    public function __construct(
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        UserRepository $repository,
        OauthService $oauthService,
        UserRequestHandler $userRH,
        OauthConnectUserRequestHandler $oauthConnectUserRH
    )
    {
        $this->userRH             = $userRH;
        $this->oauthService       = $oauthService;
        $this->session            = $session;
        $this->entityManager      = $entityManager;
        $this->repository         = $repository;
        $this->oauthConnectUserRH = $oauthConnectUserRH;
    }

    public function addOauthToUser(
        string $client,
        User $user,
        $userOauth
    ): void
    {
        /** @var Session $session */
        $session  = $this->session;
        $data     = !is_array($userOauth) ? $userOauth->toArray() : $userOauth;
        $identity = $this->oauthService->getIdentity($data, $client);
        $find     = $this->findOAuthIdentity(
            $user,
            $identity,
            $client,
            $oauthConnect
        );
        /** @var OauthConnectUserRepository $repository */
        $repository = $this->entityManager->getRepository(OauthConnectUser::class);
        if (false === $find) {
            /** @var OauthConnectUser|null $oauthConnect */
            $oauthConnect = $repository->findOauthNotUser(
                $user,
                $identity,
                $client
            );
            if (is_null($oauthConnect)) {
                $oauthConnect = new OauthConnectUser();
                $oauthConnect->setIdentity($identity);
                $oauthConnect->setRefuser($user);
                $oauthConnect->setName($client);
            }

            /** @var User $refuser */
            $refuser = $oauthConnect->getRefuser();
            if ($refuser->getId() !== $user->getId()) {
                $oauthConnect = null;
            }
        }

        if ($oauthConnect instanceof OauthConnectUser) {
            $old = clone $oauthConnect;
            $oauthConnect->setData($data);
            $this->oauthConnectUserRH->handle($old, $oauthConnect);
            $session->getFlashBag()->add('success', 'Compte associé');

            return;
        }

        $session->getFlashBag()->add(
            'warning',
            "Impossible d'associer le compte"
        );
    }

    public function ifBug(
        AbstractProvider $provider,
        array $query,
        ?string $oauth2state
    ): bool
    {
        if (is_null($oauth2state)) {
            return true;
        }

        if (!$provider instanceof AbstractProvider) {
            return true;
        }

        return (bool) (!isset($query['code']) || $oauth2state !== $query['state']);
    }

    public function postLostPassword(array $post): void
    {
        if ('' === $post['value']) {
            return;
        }

        /** @var User $user */
        $user = $this->repository->findUserEnable($post['value']);
        if (!$user instanceof User) {
            return;
        }

        $this->userRH->changeWorkflowState($user, ['lostpassword']);
    }

    /**
     * @param mixed $oauthConnect
     */
    protected function findOAuthIdentity(
        User $user,
        string $identity,
        string $client,
        &$oauthConnect = null
    ): bool
    {
        $oauthConnects = $user->getOauthConnectUsers();
        foreach ($oauthConnects as $oauthConnect) {
            $test1 = ($oauthConnect->getName() == $client);
            $test2 = ($oauthConnect->getIdentity() == $identity);
            if ($test1 && $test2) {
                return true;
            }
        }

        $oauthConnect = null;

        return false;
    }
}
