<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Block\Custom;
use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Interfaces\EntityWithParagraphInterface;
use Labstag\Repository\LayoutRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: LayoutRepository::class)]
class Layout implements EntityFrontInterface, EntityTrashInterface, EntityWithParagraphInterface
{
    use SoftDeleteableEntity;

    #[ORM\ManyToOne(targetEntity: Custom::class, inversedBy: 'layouts', cascade: ['persist'])]
    private ?Custom $custom = null;

    #[ORM\ManyToMany(targetEntity: Groupe::class, inversedBy: 'layouts')]
    private Collection $groupes;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'layout', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $paragraphs;

    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $url = [];

    public function __construct()
    {
        $this->paragraphs = new ArrayCollection();
        $this->groupes    = new ArrayCollection();
    }

    public function addGroupe(Groupe $groupe): self
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes->add($groupe);
        }

        return $this;
    }

    public function addParagraph(Paragraph $paragraph): self
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs[] = $paragraph;
            $paragraph->setLayout($this);
        }

        return $this;
    }

    public function getCustom(): ?Custom
    {
        return $this->custom;
    }

    /**
     * @return Collection<int, Groupe>
     */
    public function getGroupes(): Collection
    {
        return $this->groupes;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, Paragraph>
     */
    public function getParagraphs(): Collection
    {
        return $this->paragraphs;
    }

    public function getUrl(): ?array
    {
        return $this->url;
    }

    public function removeGroupe(Groupe $groupe): self
    {
        $this->groupes->removeElement($groupe);

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): self
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getLayout() === $this) {
            $paragraph->setLayout(null);
        }

        return $this;
    }

    public function setCustom(?Custom $custom): self
    {
        $this->custom = $custom;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setUrl(?array $url): self
    {
        $this->url = $url;

        return $this;
    }
}
