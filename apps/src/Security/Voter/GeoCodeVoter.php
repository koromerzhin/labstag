<?php

namespace Labstag\Security\Voter;

use Labstag\Entity\GeoCode;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GeoCodeVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        unset($attribute);

        return !(!$subject instanceof GeoCode);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        unset($attribute, $subject, $token);

        return true;
    }
}
