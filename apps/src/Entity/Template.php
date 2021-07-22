<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Repository\TemplateRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TemplateRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Template
{
    use SoftDeleteableEntity;

    /**
     * @Gedmo\Slug(updatable=false, fields={"name"})
     * @ORM\Column(type="string", length=255)
     */
    protected $code;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank
     */
    protected $html;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank
     */
    protected $text;

    public function __toString()
    {
        return $this->getName();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function setHtml(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }
}
