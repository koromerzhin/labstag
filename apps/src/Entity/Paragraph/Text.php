<?php

namespace Labstag\Entity\Paragraph;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Entity\Paragraph;
use Labstag\Interfaces\EntityInterface;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Repository\Paragraph\TextRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: TextRepository::class)]
#[ORM\Table(name: 'paragraph_text')]
class Text implements Stringable, EntityParagraphInterface, EntityInterface
{

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: Paragraph::class, inversedBy: 'texts', cascade: ['persist'])]
    private ?Paragraph $paragraph = null;

    public function __toString(): string
    {
        /** @var Paragraph $paragraph */
        $paragraph = $this->getParagraph();

        return (string) $paragraph->getType();
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getParagraph(): ?Paragraph
    {
        return $this->paragraph;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setParagraph(?Paragraph $paragraph): self
    {
        $this->paragraph = $paragraph;

        return $this;
    }
}
