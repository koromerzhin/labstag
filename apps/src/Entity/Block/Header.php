<?php

namespace Labstag\Entity\Block;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Entity\Block;
use Labstag\Repository\Block\HeaderRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * @ORM\Table(name="block_header")
 *
 * @ORM\Entity(repositoryClass=HeaderRepository::class)
 */
class Header
{

    /**
     * @ORM\ManyToOne(targetEntity=Block::class, inversedBy="headers", cascade={"persist"})
     */
    private $block;

    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="CUSTOM")
     *
     * @ORM\Column(type="guid", unique=true)
     *
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Link::class, mappedBy="header", cascade={"persist"}, orphanRemoval=true)
     */
    private $links;

    public function __toString(): string
    {
        return (string) $this->getBlock()->getTitle();
    }

    public function __construct()
    {
        $this->links = new ArrayCollection();
    }

    public function addLink(Link $link): self
    {
        if (!$this->links->contains($link)) {
            $this->links[] = $link;
            $link->setHeader($this);
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
     * @return Collection<int, Link>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function removeLink(Link $link): self
    {
        // set the owning side to null (unless already changed)
        if ($this->links->removeElement($link) && $link->getHeader() === $this) {
            $link->setHeader(null);
        }

        return $this;
    }

    public function setBlock(?Block $block): self
    {
        $this->block = $block;

        return $this;
    }
}
