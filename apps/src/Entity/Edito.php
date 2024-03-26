<?php

namespace Labstag\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Annotation\UploadableField;
use Labstag\Entity\Traits\StateableEntity;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Interfaces\EntityWithParagraphInterface;
use Labstag\Interfaces\PublicInterface;
use Labstag\Repository\EditoRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: EditoRepository::class)]
class Edito implements Stringable, PublicInterface, EntityTrashInterface, EntityWithParagraphInterface
{
    use SoftDeleteableEntity;
    use StateableEntity;

    #[ORM\ManyToOne(targetEntity: Attachment::class, inversedBy: 'editos', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'fond_id')]
    private ?Attachment $attachment = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[ORM\Column(name: 'published', type: 'datetime')]
    private ?DateTimeInterface $dateTime = null;

    #[UploadableField(filename: 'fond', path: 'edito/fond', slug: 'title')]
    private mixed $file;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Meta::class, mappedBy: 'edito', cascade: ['persist'], orphanRemoval: true)]
    private Collection $metas;

    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'edito', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $paragraphs;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    #[Assert\NotBlank]
    private string $title;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'editos', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'refuser_id', nullable: false)]
    private ?UserInterface $user = null;

    public function __construct()
    {
        $this->paragraphs = new ArrayCollection();
        $this->metas      = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addMeta(Meta $meta): self
    {
        if (!$this->metas->contains($meta)) {
            $this->metas[] = $meta;
            $meta->setEdito($this);
        }

        return $this;
    }

    public function addParagraph(Paragraph $paragraph): self
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs[] = $paragraph;
            $paragraph->setEdito($this);
        }

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getFile(): mixed
    {
        return $this->file;
    }

    public function getFond(): ?Attachment
    {
        return $this->attachment;
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

    /**
     * @return Collection<int, Paragraph>
     */
    public function getParagraphs(): Collection
    {
        return $this->paragraphs;
    }

    public function getPublished(): ?DateTimeInterface
    {
        return $this->dateTime;
    }

    public function getRefuser(): ?UserInterface
    {
        return $this->user;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function removeMeta(Meta $meta): self
    {
        // set the owning side to null (unless already changed)
        if ($this->metas->removeElement($meta) && $meta->getEdito() === $this) {
            $meta->setEdito(null);
        }

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): self
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getEdito() === $this) {
            $paragraph->setEdito(null);
        }

        return $this;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setFile(mixed $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function setFond(?Attachment $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function setPublished(DateTimeInterface $dateTime): self
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    public function setRefuser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
