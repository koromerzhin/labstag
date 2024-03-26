<?php

namespace Labstag\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Labstag\Entity\AddressUser;
use Labstag\Entity\LinkUser;
use Labstag\Entity\OauthConnectUser;
use Labstag\Entity\User;
use Labstag\Interfaces\UserDataInterface;
use Labstag\Lib\EventListenerLib;

#[AsDoctrineListener(event: Events::postPersist)]
class UserDataListener extends EventListenerLib
{
    public function postPersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->logActivity('persist', $lifecycleEventArgs);
    }

    private function logActivity(string $action, LifecycleEventArgs $lifecycleEventArgs): void
    {
        $object = $lifecycleEventArgs->getObject();
        if (!$object instanceof UserDataInterface) {
            return;
        }

        $this->logger->info($action.' '.$object::class);

        $this->setOauthConnectUser($object);
        $this->setLinkUser($object);
        $this->setAddressUser($object);
    }

    private function setAddressUser(UserDataInterface $userData): void
    {
        if (!$userData instanceof AddressUser) {
            return;
        }

        /** @var User $user */
        $user = $userData->getRefuser();

        $this->userMailService->checkNewAddress(
            $user,
            $userData
        );
    }

    private function setLinkUser(UserDataInterface $userData): void
    {
        if (!$userData instanceof LinkUser) {
            return;
        }

        /** @var User $user */
        $user = $userData->getRefuser();
        $this->userMailService->checkNewLink(
            $user,
            $userData
        );
    }

    private function setOauthConnectUser(UserDataInterface $userData): void
    {
        if (!$userData instanceof OauthConnectUser) {
            return;
        }

        /** @var User $user */
        $user = $userData->getRefuser();
        $this->userMailService->checkNewOauthConnectUser(
            $user,
            $userData
        );
    }
}
