<?php

namespace Labstag\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\EmailUser;
use Labstag\Entity\User;
use Labstag\Event\UserEntityEvent;
use Labstag\RequestHandler\EmailUserRequestHandler;
use Labstag\Service\UserMailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserEntitySubscriber implements EventSubscriberInterface
{

    protected EmailUserRequestHandler $emailUserRH;

    protected EntityManagerInterface $entityManager;

    protected UserPasswordEncoderInterface $passwordEncoder;

    protected SessionInterface $session;

    protected UserMailService $userMailService;

    public function __construct(
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        UserMailService $userMailService,
        EmailUserRequestHandler $emailUserRH
    )
    {
        $this->emailUserRH     = $emailUserRH;
        $this->userMailService = $userMailService;
        $this->entityManager   = $entityManager;
        $this->session         = $session;
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getSubscribedEvents()
    {
        return [UserEntityEvent::class => 'onUserEntityEvent'];
    }

    public function onUserEntityEvent(UserEntityEvent $event): void
    {
        $oldEntity = $event->getOldEntity();
        $newEntity = $event->getNewEntity();
        $this->setPassword($newEntity);
        $this->setPrincipalMail($oldEntity, $newEntity);
        $this->setChangePassword($oldEntity, $newEntity);
    }

    protected function setChangePassword(User $oldEntity, User $newEntity): void
    {
        if ($oldEntity->getState() == $newEntity->getState()) {
            return;
        }

        if ('lostpassword' != $oldEntity->getState()) {
            return;
        }

        /** @var Session $session */
        $session = $this->session;
        $this->userMailService->changePassword($newEntity);
        $session->getFlashBag()->add(
            'success',
            'Changement de mot de passe effectué'
        );
    }

    protected function setPassword(User $user): void
    {
        $plainPassword = $user->getPlainPassword();
        if ('' === $plainPassword || is_null($plainPassword)) {
            return;
        }

        $encodePassword = $this->passwordEncoder->encodePassword(
            $user,
            $plainPassword
        );

        $user->setPassword($encodePassword);
        if ('valider' == $user->getState()) {
            $this->userMailService->changePassword($user);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        /** @var Session $session */
        $session = $this->session;
        $session->getFlashBag()->add(
            'success',
            'Mot de passe changé'
        );
    }

    protected function setPrincipalMail(User $oldEntity, User $newEntity): void
    {
        if ($oldEntity->getEmail() == $newEntity->getEmail()) {
            return;
        }

        $adresse = $newEntity->getEmail();
        $emails  = $newEntity->getEmailUsers();
        $trouver = false;
        foreach ($emails as $emailUser) {
            /* @var EmailUser $emailUser */
            $emailUser->setPrincipal(false);
            if ($emailUser->getAdresse() === $adresse) {
                $emailUser->setPrincipal(true);
                $trouver = true;
            }

            $this->entityManager->persist($emailUser);
        }

        if ('valider' == $newEntity->getState()) {
            $this->userMailService->changeEmailPrincipal($newEntity);
        }

        $this->entityManager->flush();
        /** @var Session $session */
        $session = $this->session;
        $session->getFlashBag()->add(
            'success',
            'Email principal changé'
        );

        if ($trouver) {
            return;
        }

        $emailUser = new EmailUser();
        $old       = clone $emailUser;
        $emailUser->setRefuser($newEntity);
        $emailUser->setPrincipal(true);
        $emailUser->setAdresse($adresse);
        $this->emailUserRH->handle($old, $emailUser);
        $this->emailUserRH->changeWorkflowState($emailUser, ['submit', 'valider']);
    }
}
