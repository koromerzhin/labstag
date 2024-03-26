<?php

namespace Labstag\Service;

use Labstag\Interfaces\DomainInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class DomainService
{

    protected $rewindableGenerator;

    public function __construct(
        #[TaggedIterator('domainsclass')]
        iterable $rewindableGenerator
    )
    {
        $this->rewindableGenerator = $rewindableGenerator;
    }

    public function getDomain(string $entity): ?DomainInterface
    {
        $return = null;
        foreach ($this->rewindableGenerator as $domain) {
            /** @var DomainInterface $domain */
            if ($domain->getEntity() === $entity) {
                $return = $domain;

                break;
            }
        }

        return $return;
    }

    public function getTitles(): array
    {
        $titles = [];
        foreach ($this->rewindableGenerator as $domain) {
            /** @var DomainInterface $domain */
            $titles = array_merge($titles, $domain->getTitles());
        }

        return $titles;
    }
}
