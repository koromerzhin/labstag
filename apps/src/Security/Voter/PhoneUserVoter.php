<?php

namespace Labstag\Security\Voter;

use Labstag\Entity\PhoneUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PhoneUserVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        unset($attribute);

        return $subject instanceof PhoneUser;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        unset($attribute, $subject, $token);

        return true;
    }
}
