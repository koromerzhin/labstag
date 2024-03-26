<?php

namespace Labstag\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Annotation\Uploadable;
use Labstag\Annotation\UploadableField;
use Labstag\Entity\Traits\StateableEntity;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Interfaces\EntityWithParagraphInterface;
use Labstag\Interfaces\PublicInterface;
use Labstag\Repository\PostRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Uploadable]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post implements Stringable, PublicInterface, EntityTrashInterface, EntityWithParagraphInterface
{
    use SoftDeleteableEntity;
    use StateableEntity;

    #[ORM\ManyToOne(targetEntity: Attachment::class, inversedBy: 'posts', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'img_id')]
    private ?Attachment $attachment = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'posts', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'refcategory_id')]
    private ?Category $category = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $created = null;

    #[UploadableField(filename: 'img', path: 'post/img', slug: 'title')]
    private mixed $file;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\ManyToMany(targetEntity: Libelle::class, mappedBy: 'posts', cascade: ['persist'])]
    private Collection $libelles;

    #[ORM\OneToMany(targetEntity: Meta::class, mappedBy: 'post', cascade: ['persist'], orphanRemoval: true)]
    private Collection $metas;

    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'post', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $paragraphs;

    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $published = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $remark = null;

    #[Gedmo\Slug(updatable: false, fields: ['title'])]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    private ?string $title = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $updated = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts', cascade: ['persist'])]
    #[Assert\NotBlank]
    #[ORM\JoinColumn(name: 'refuser_id', nullable: false)]
    private ?UserInterface $user = null;

    public function __construct()
    {
        $this->libelles   = new ArrayCollection();
        $this->paragraphs = new ArrayCollection();
        $this->metas      = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addLibelle(Libelle $libelle): self
    {
        if (!$this->libelles->contains($libelle)) {
            $this->libelles[] = $libelle;
            $libelle->addPost($this);
        }

        return $this;
    }

    public function addMeta(Meta $meta): self
    {
        if (!$this->metas->contains($meta)) {
            $this->metas[] = $meta;
            $meta->setPost($this);
        }

        return $this;
    }

    public function addParagraph(Paragraph $paragraph): self
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs[] = $paragraph;
            $paragraph->setPost($this);
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

    public function getFile(): mixed
    {
        return $this->file;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getImg(): ?Attachment
    {
        return $this->attachment;
    }

    public function getLibelles(): Collection
    {
        return $this->libelles;
    }

    /**
     * @return Collection<int, Meta>
     */
    public function getMetas(): Collection
    {
        return $this->metas;
    }

    /**
     * @return Collection<int, Paragraph>
     */
    public function getParagraphs(): Collection
    {
        return $this->paragraphs;
    }

    public function getPublished(): ?DateTimeInterface
    {
        return $this->published;
    }

    public function getRefcategory(): ?Category
    {
        return $this->category;
    }

    public function getRefuser(): ?UserInterface
    {
        return $this->user;
    }

    public function getRemark(): ?bool
    {
        return $this->remark;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getUpdated(): ?DateTimeInterface
    {
        return $this->updated;
    }

    public function removeLibelle(Libelle $libelle): self
    {
        if ($this->libelles->removeElement($libelle)) {
            $libelle->removePost($this);
        }

        return $this;
    }

    public function removeMeta(Meta $meta): self
    {
        $this->removeElementPost(
            element: $this->metas,
            meta: $meta
        );

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): self
    {
        $this->removeElementPost(
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

    public function setFile(mixed $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function setImg(?Attachment $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function setPublished(DateTimeInterface $dateTime): self
    {
        $this->published = $dateTime;

        return $this;
    }

    public function setRefcategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function setRefuser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setRemark(bool $remark): self
    {
        $this->remark = $remark;

        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setUpdated(DateTimeInterface $dateTime): self
    {
        $this->updated = $dateTime;

        return $this;
    }

    private function removeElementPost(
        Collection $element,
        ?Meta $meta = null,
        ?Paragraph $paragraph = null
    ): void
    {
        if (is_null($meta) && is_null($paragraph)) {
            return;
        }

        $variable = is_null($meta) ? $paragraph : $meta;
        if ($element->removeElement($variable) && $variable->getPost() === $this) {
            $variable->setPost(null);
        }
    }
}
