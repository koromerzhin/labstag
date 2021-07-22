<?php

namespace Labstag\Event;

use Labstag\Entity\AdresseUser;
use Labstag\Entity\EmailUser;
use Labstag\Entity\LienUser;
use Labstag\Entity\OauthConnectUser;
use Labstag\Entity\PhoneUser;

class UserCollectionEvent
{

    protected array $adresseUser = [];

    protected array $emailUser = [];

    protected array $lienUser = [];

    protected array $oauthConnectUser = [];

    protected array $phoneUser = [];

    public function addAdresseUser(AdresseUser $old, AdresseUser $new): void
    {
        $this->adresseUser = [
            'old' => $old,
            'new' => $new,
        ];
    }

    public function addEmailUser(EmailUser $old, EmailUser $new): void
    {
        $this->emailUser = [
            'old' => $old,
            'new' => $new,
        ];
    }

    public function addLienUser(LienUser $old, LienUser $new): void
    {
        $this->lienUser = [
            'old' => $old,
            'new' => $new,
        ];
    }

    public function addOauthConnectUser(
        OauthConnectUser $old,
        OauthConnectUser $new
    ): void
    {
        $this->oauthConnectUser = [
            'old' => $old,
            'new' => $new,
        ];
    }

    public function addPhoneUser(PhoneUser $old, PhoneUser $new): void
    {
        $this->phoneUser = [
            'old' => $old,
            'new' => $new,
        ];
    }

    public function getAdresseUser(): array
    {
        return $this->adresseUser;
    }

    public function getEmailUser(): array
    {
        return $this->emailUser;
    }

    public function getLienUser(): array
    {
        return $this->lienUser;
    }

    public function getOauthConnectUser(): array
    {
        return $this->oauthConnectUser;
    }

    public function getPhoneUser(): array
    {
        return $this->phoneUser;
    }
}
