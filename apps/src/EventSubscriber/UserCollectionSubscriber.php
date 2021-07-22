<?php

namespace Labstag\EventSubscriber;

use Labstag\Entity\AdresseUser;
use Labstag\Entity\LienUser;
use Labstag\Entity\OauthConnectUser;
use Labstag\Event\UserCollectionEvent;
use Labstag\Service\UserMailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserCollectionSubscriber implements EventSubscriberInterface
{

    protected UserMailService $userMailService;

    public function __construct(UserMailService $userMailService)
    {
        $this->userMailService = $userMailService;
    }

    public static function getSubscribedEvents()
    {
        return [UserCollectionEvent::class => 'onUserCollectionEvent'];
    }

    public function onUserCollectionEvent(UserCollectionEvent $event): void
    {
        $oauthConnectUser = $event->getOauthConnectUser();
        $lienUser         = $event->getLienUser();
        $adresseUser      = $event->getAdresseUser();

        $this->setOauthConnectUser($oauthConnectUser);
        $this->setLienUser($lienUser);
        $this->setAdresseUser($adresseUser);
    }

    protected function setAdresseUser(array $data): void
    {
        if (0 == count($data)) {
            return;
        }

        /** @var AdresseUser $old */
        $old = $data['old'];
        /** @var AdresseUser $new */
        $new = $data['new'];
        if ($old->getId() == $new->getId()) {
            return;
        }

        $this->userMailService->checkNewAdresse($new->getRefuser(), $new);
    }

    protected function setLienUser(array $data): void
    {
        if (0 == count($data)) {
            return;
        }

        /** @var LienUser $old */
        $old = $data['old'];
        /** @var LienUser $new */
        $new = $data['new'];
        if ($old->getId() == $new->getId()) {
            return;
        }

        $this->userMailService->checkNewLink($new->getRefuser(), $new);
    }

    protected function setOauthConnectUser(array $data): void
    {
        if (0 == count($data)) {
            return;
        }

        /** @var OauthConnectUser $old */
        $old = $data['old'];
        /** @var OauthConnectUser $new */
        $new = $data['new'];
        if ($old->getId() == $new->getId()) {
            return;
        }

        $this->userMailService->checkNewOauthConnectUser(
            $new->getRefuser(),
            $new
        );
    }
}
