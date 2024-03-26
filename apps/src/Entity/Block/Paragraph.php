<?php

namespace Labstag\Entity\Block;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Entity\Block;
use Labstag\Interfaces\EntityBlockInterface;
use Labstag\Repository\Block\ParagraphRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: ParagraphRepository::class)]
#[ORM\Table(name: 'block_paragraph')]
class Paragraph implements Stringable, EntityBlockInterface
{

    #[ORM\ManyToOne(targetEntity: Block::class, inversedBy: 'paragraphs', cascade: ['persist'])]
    private ?Block $block = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    public function __toString(): string
    {
        /** @var Block $block */
        $block = $this->getBlock();

        return (string) $block->getTitle();
    }

    public function getBlock(): ?Block
    {
        return $this->block;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setBlock(?Block $block): self
    {
        $this->block = $block;

        return $this;
    }
}
