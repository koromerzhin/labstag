<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Repository\TemplateRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: TemplateRepository::class)]
class Template implements Stringable, EntityTrashInterface
{
    use SoftDeleteableEntity;

    #[Gedmo\Slug(updatable: false, fields: ['name'])]
    #[ORM\Column(type: 'string', length: 255)]
    private string $code;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    private ?string $html = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    private ?string $text = null;

    public function __toString(): string
    {
        return (string) $this->getName();
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
