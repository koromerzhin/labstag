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
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class UploadableField implements MappingAttribute
{

    private ?string $filename = null;

    private ?string $path = null;

    private ?string $slug = null;

    public function __construct(
        ?string $filename = null,
        ?string $path = null,
        ?string $slug = null
    )
    {
        if (null === $filename || '' === $filename) {
            throw new InvalidArgumentException("L'annotation UplodableField doit avoir un attribut 'filename'");
        }

        if (null === $path || '' === $path) {
            throw new InvalidArgumentException("L'annotation UplodableField doit avoir un attribut 'path'");
        }

        if (null === $slug || '' === $slug) {
            throw new InvalidArgumentException("L'annotation UplodableField doit avoir un attribut 'slug'");
        }

        $this->filename = $filename;
        $this->path     = $path;
        $this->slug     = $slug;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getSlug(): string
    {
        return strtolower((string) $this->slug);
    }
}
