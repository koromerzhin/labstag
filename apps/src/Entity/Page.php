<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Handler\TreeSlugHandler;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Interfaces\EntityWithParagraphInterface;
use Labstag\Interfaces\PublicInterface;
use Labstag\Repository\PageRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page implements Stringable, PublicInterface, EntityTrashInterface, EntityWithParagraphInterface
{
    use SoftDeleteableEntity;

    #[ORM\ManyToMany(targetEntity: Block::class, mappedBy: 'notinpages')]
    private Collection $blocks;

    #[ORM\OneToMany(targetEntity: Page::class, mappedBy: 'page', cascade: ['persist'], orphanRemoval: true)]
    private Collection $children;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Meta::class, mappedBy: 'page', cascade: ['persist'], orphanRemoval: true)]
    private Collection $metas;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'children', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?Page $page = null;

    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'page', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $paragraphs;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $password = null;

    #[Gedmo\Slug(fields: ['name'])]
    #[Gedmo\SlugHandler(
        class: TreeSlugHandler::class,
        options: [
            'parentRelationField' => 'page',
            'separator'           => '/',
        ]
    )]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->children   = new ArrayCollection();
        $this->paragraphs = new ArrayCollection();
        $this->metas      = new ArrayCollection();
        $this->blocks     = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function addBlock(Block $block): self
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks->add($block);
            $block->addNotinpage($this);
        }

        return $this;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function addMeta(Meta $meta): self
    {
        if (!$this->metas->contains($meta)) {
            $this->metas[] = $meta;
            $meta->setPage($this);
        }

        return $this;
    }

    public function addParagraph(Paragraph $paragraph): self
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs[] = $paragraph;
            $paragraph->setPage($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Block>
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Meta>
     */
    public function getMetas(): Collection
    {
        return $this->metas;
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

    public function getParent(): ?Page
    {
        return $this->page;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function removeBlock(Block $block): self
    {
        if ($this->blocks->removeElement($block)) {
            $block->removeNotinpage($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        // set the owning side to null (unless already changed)
        if ($this->children->removeElement($child) && $child->getParent() === $this) {
            $child->setParent(null);
        }

        return $this;
    }

    public function removeMeta(Meta $meta): self
    {
        $this->removeElementPage(
            element: $this->metas,
            meta: $meta
        );

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): self
    {
        $this->removeElementPage(
            element: $this->paragraphs,
            paragraph: $paragraph
        );

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setParent(?self $parent): self
    {
        $this->page = $parent;

        return $this;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    private function removeElementPage(
        Collection $element,
        ?Meta $meta = null,
        ?Paragraph $paragraph = null
    ): void
    {
        if (is_null($meta) && is_null($paragraph)) {
            return;
        }

        $variable = is_null($meta) ? $paragraph : $meta;

        if ($element->removeElement($variable) && $variable->getPage() === $this) {
            $variable->setPage(null);
        }
    }
}
