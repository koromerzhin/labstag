<?php

namespace Labstag\Security\Voter;

use Labstag\Entity\Configuration;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConfigurationVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        unset($attribute);

        return $subject instanceof Configuration;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        unset($attribute, $subject, $token);

        return true;
    }
}
