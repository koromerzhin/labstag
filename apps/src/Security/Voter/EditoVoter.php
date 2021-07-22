<?php

namespace Labstag\Security\Voter;

use Labstag\Entity\Edito;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EditoVoter extends Voter
{
    protected function canEdit(Edito $entity, TokenInterface $token): bool
    {
        unset($token);
        $state = $entity->getState();

        return !(in_array($state, ['publie', 'rejete']));
    }

    protected function supports($attribute, $subject)
    {
        unset($attribute);

        return $subject instanceof Edito;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case 'edit':
                $return = $this->canEdit($subject, $token);
                break;
            default:
                $return = true;
        }

        return $return;
    }
}
