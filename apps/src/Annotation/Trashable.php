<?php

namespace Labstag\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\ORM\Mapping\MappingAttribute;
use InvalidArgumentException;

/**
 * @Annotation
 *
 * @NamedArgumentConstructor
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Trashable implements MappingAttribute
{

    private ?string $url = null;

    public function __construct(
        ?string $url = null
    )
    {
        if (null === $url || '' === $url) {
            throw new InvalidArgumentException("L'annotation Trashable doit avoir un attribut 'url'");
        }

        $this->url = $url;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}
