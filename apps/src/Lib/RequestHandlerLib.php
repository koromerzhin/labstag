<?php

namespace Labstag\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\User;
use Labstag\Event\UserCollectionEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Registry;

abstract class RequestHandlerLib
{

    protected EventDispatcherInterface $dispatcher;

    protected EntityManagerInterface $entityManager;

    protected Registry $workflows;

    public function __construct(
        EntityManagerInterface $entityManager,
        Registry $workflows,
        EventDispatcherInterface $dispatcher
    )
    {
        $this->entityManager = $entityManager;
        $this->workflows     = $workflows;
        $this->dispatcher    = $dispatcher;
    }

    public function changeWorkflowState($entity, array $states)
    {
        if (!$this->workflows->has($entity)) {
            return;
        }

        $workflow = $this->workflows->get($entity);
        foreach ($states as $state) {
            if ($workflow->can($entity, $state)) {
                $workflow->apply($entity, $state);
            }
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function handle($oldEntity, $entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        if ($oldEntity->getId() != $entity->getId()) {
            $this->initWorkflow($entity);
        }
    }

    protected function initWorkflow($entity)
    {
        if (!$this->workflows->has($entity)) {
            return;
        }

        $workflow    = $this->workflows->get($entity);
        $definition  = $workflow->getDefinition();
        $transitions = $definition->getTransitions();
        foreach ($transitions as $transition) {
            $name = $transition->getName();
            if ($workflow->can($entity, $name)) {
                $workflow->apply($entity, $name);
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                break;
            }
        }
    }

    protected function setArrayCollectionUser(User $entity)
    {
        $userCollectionEvent = new UserCollectionEvent();
        $oauthConnectUsers   = $entity->getOauthConnectUsers();
        foreach ($oauthConnectUsers as $row) {
            /** @var OauthConnectUser $row */
            $old = clone $row;
            $row->setRefuser($entity);
            $userCollectionEvent->addOauthConnectUser($old, $row);
        }

        $liensUsers = $entity->getLienUsers();
        foreach ($liensUsers as $row) {
            /** @var LienUser $row */
            $old = clone $row;
            $row->setRefuser($entity);
            $userCollectionEvent->addLienUser($old, $row);
        }

        $emailUsers = $entity->getEmailUsers();
        foreach ($emailUsers as $row) {
            /** @var EmailUser $row */
            $old = clone $row;
            $row->setRefuser($entity);
            $userCollectionEvent->addEmailUser($old, $row);
        }

        $phoneUsers = $entity->getPhoneUsers();
        foreach ($phoneUsers as $row) {
            /** @var PhoneUser $row */
            $old = clone $row;
            $row->setRefuser($entity);
            $userCollectionEvent->addPhoneUser($old, $row);
        }

        $adresseUsers = $entity->getAdresseUsers();
        foreach ($adresseUsers as $row) {
            /** @var AdresseUser $row */
            $old = clone $row;
            $row->setRefuser($entity);
            $userCollectionEvent->addAdresseUser($old, $row);
        }
    }
}
