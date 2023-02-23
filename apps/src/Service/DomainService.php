<?php

namespace Labstag\Service;

use Labstag\Lib\DomainLib;

class DomainService
{
    public function __construct(
        protected $domainsclass
    )
    {
    }

    public function getDomain($entity): DomainLib
    {
        $return = null;
        foreach ($this->domainsclass as $domain) {
            if ($domain->getEntity() == $entity) {
                $return = $domain;

                break;
            }
        }

        return $return;
    }

    public function getTitles()
    {
        $titles = [];
        foreach ($this->domainsclass as $domain) {
            $titles = array_merge($titles, $domain->getTitles());
        }

        return $titles;
    }
}
