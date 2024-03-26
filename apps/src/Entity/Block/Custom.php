<?php

namespace Labstag\Entity\Block;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Entity\Block;
use Labstag\Entity\Layout;
use Labstag\Interfaces\EntityBlockInterface;
use Labstag\Repository\Block\CustomRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: CustomRepository::class)]
#[ORM\Table(name: 'block_custom')]
class Custom implements Stringable, EntityBlockInterface
{

    #[ORM\ManyToOne(targetEntity: Block::class, inversedBy: 'customs', cascade: ['persist'])]
    private ?Block $block = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Layout::class, mappedBy: 'custom', cascade: ['persist'], orphanRemoval: true)]
    private Collection $layouts;

    public function __construct()
    {
        $this->layouts = new ArrayCollection();
    }

    public function __toString(): string
    {
        /** @var Block $block */
        $block = $this->getBlock();

        return (string) $block->getTitle();
    }

    public function addLayout(Layout $layout): self
    {
        if (!$this->layouts->contains($layout)) {
            $this->layouts[] = $layout;
            $layout->setCustom($this);
        }

        return $this;
    }

    public function getBlock(): ?Block
    {
        return $this->block;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Layout>
     */
    public function getLayouts(): Collection
    {
        return $this->layouts;
    }

    public function removeLayout(Layout $layout): self
    {
        // set the owning side to null (unless already changed)
        if ($this->layouts->removeElement($layout) && $layout->getCustom() === $this) {
            $layout->setCustom(null);
        }

        return $this;
    }

    public function setBlock(?Block $block): self
    {
        $this->block = $block;

        return $this;
    }
}
