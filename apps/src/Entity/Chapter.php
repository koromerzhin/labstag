<?php

namespace Labstag\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Traits\StateableEntity;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Interfaces\EntityWithParagraphInterface;
use Labstag\Interfaces\PublicInterface;
use Labstag\Repository\ChapterRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: ChapterRepository::class)]
class Chapter implements PublicInterface, EntityTrashInterface, EntityWithParagraphInterface
{
    use SoftDeleteableEntity;
    use StateableEntity;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $created = null;

    #[ORM\ManyToOne(targetEntity: History::class, inversedBy: 'chapters', cascade: ['persist'])]
    #[Assert\NotBlank]
    #[ORM\JoinColumn(name: 'refhistory_id', nullable: false)]
    private ?History $history = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Meta::class, mappedBy: 'chapter', cascade: ['persist'], orphanRemoval: true)]
    private Collection $metas;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'integer')]
    private int $pages = 0;

    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'chapter', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $paragraphs;

    #[ORM\Column(type: 'integer')]
    private ?int $position = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $published = null;

    #[Gedmo\Slug(updatable: false, fields: ['name'])]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $slug = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $updated = null;

    public function __construct()
    {
        $this->metas      = new ArrayCollection();
        $this->paragraphs = new ArrayCollection();
    }

    public function addMeta(Meta $meta): self
    {
        if (!$this->metas->contains($meta)) {
            $this->metas[] = $meta;
            $meta->setChapter($this);
        }

        return $this;
    }

    public function addParagraph(Paragraph $paragraph): self
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs[] = $paragraph;
            $paragraph->setChapter($this);
        }

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getCreated(): ?DateTimeInterface
    {
        return $this->created;
    }

    public function getHistory(): ?History
    {
        return $this->history;
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

    public function getPages(): ?int
    {
        return $this->pages;
    }

    /**
     * @return Collection<int, Paragraph>
     */
    public function getParagraphs(): Collection
    {
        return $this->paragraphs;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getPublished(): ?DateTimeInterface
    {
        return $this->published;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getUpdated(): ?DateTimeInterface
    {
        return $this->updated;
    }

    public function removeMeta(Meta $meta): self
    {
        $this->removeElementChapter(
            element: $this->metas,
            meta: $meta
        );

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): self
    {
        $this->removeElementChapter(
            element: $this->paragraphs,
            paragraph: $paragraph
        );

        return $this;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setCreated(DateTimeInterface $dateTime): self
    {
        $this->created = $dateTime;

        return $this;
    }

    public function setHistory(?History $history): self
    {
        $this->history = $history;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setPages(int $pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function setPublished(DateTimeInterface $dateTime): self
    {
        $this->published = $dateTime;

        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function setUpdated(DateTimeInterface $dateTime): self
    {
        $this->updated = $dateTime;

        return $this;
    }

    private function removeElementChapter(
        Collection $element,
        ?Meta $meta = null,
        ?Paragraph $paragraph = null
    ): void
    {
        if (is_null($meta) && is_null($paragraph)) {
            return;
        }

        $variable = is_null($meta) ? $paragraph : $meta;
        if ($element->removeElement($variable) && $variable->getChapter() === $this) {
            $variable->setChapter(null);
        }
    }
}
