<?php

namespace Labstag\Security\Voter;

use Labstag\Entity\NoteInterne;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class NoteInterneVoter extends Voter
{
    protected function canEdit(NoteInterne $entity, TokenInterface $token): bool
    {
        unset($token);
        $state = $entity->getState();

        return !(in_array($state, ['publie', 'rejete']));
    }

    protected function supports($attribute, $subject)
    {
        unset($attribute);

        return $subject instanceof NoteInterne;
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
