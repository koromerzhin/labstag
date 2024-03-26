<?php

namespace Labstag\Entity\Block;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Interfaces\EntityInterface;
use Labstag\Repository\Block\LinkRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
#[ORM\Table(name: 'block_link')]
class Link implements Stringable, EntityInterface
{

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $external = null;

    #[ORM\ManyToOne(targetEntity: Footer::class, inversedBy: 'links', cascade: ['persist'])]
    private ?Footer $footer = null;

    #[ORM\ManyToOne(targetEntity: Header::class, inversedBy: 'links', cascade: ['persist'])]
    private ?Header $header = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $url = null;

    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function getExternal(): ?bool
    {
        return $this->external;
    }

    public function getFooter(): ?Footer
    {
        return $this->footer;
    }

    public function getHeader(): ?Header
    {
        return $this->header;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setExternal(?bool $external): self
    {
        $this->external = $external;

        return $this;
    }

    public function setFooter(?Footer $footer): self
    {
        $this->footer = $footer;

        return $this;
    }

    public function setHeader(?Header $header): self
    {
        $this->header = $header;

        return $this;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
