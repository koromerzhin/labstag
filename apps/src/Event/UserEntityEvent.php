<?php

namespace Labstag\Event;

use Labstag\Entity\User;

class UserEntityEvent
{

    protected User $newEntity;

    protected User $oldEntity;

    public function __construct(User $oldEntity, User $newEntity)
    {
        $this->oldEntity = $oldEntity;
        $this->newEntity = $newEntity;
    }

    public function getNewEntity(): User
    {
        return $this->newEntity;
    }

    public function getOldEntity(): User
    {
        return $this->oldEntity;
    }
}
