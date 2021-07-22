<?php

namespace Labstag\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use InvalidArgumentException;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class UploadableField
{

    protected $filename;

    protected $path;

    protected $slug;

    public function __construct(array $options)
    {
        if (empty($options['filename'])) {
            throw new InvalidArgumentException("L'annotation UplodableField doit avoir un attribut 'filename'");
        }

        if (empty($options['path'])) {
            throw new InvalidArgumentException("L'annotation UplodableField doit avoir un attribut 'path'");
        }

        if (empty($options['slug'])) {
            throw new InvalidArgumentException("L'annotation UplodableField doit avoir un attribut 'slug'");
        }

        $this->filename = $options['filename'];
        $this->path     = $options['path'];
        $this->slug     = $options['slug'];
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getSlug()
    {
        return $this->slug;
    }
}
