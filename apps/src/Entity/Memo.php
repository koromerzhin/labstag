<?php

namespace Labstag\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Annotation\Uploadable;
use Labstag\Annotation\UploadableField;
use Labstag\Entity\Traits\StateableEntity;
use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Interfaces\EntityWithParagraphInterface;
use Labstag\Repository\MemoRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Uploadable]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: MemoRepository::class)]
class Memo implements Stringable, EntityFrontInterface, EntityTrashInterface, EntityWithParagraphInterface
{
    use SoftDeleteableEntity;
    use StateableEntity;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\GreaterThanOrEqual(propertyPath: 'dateStart')]
    protected ?DateTime $dateEnd = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\LessThanOrEqual(propertyPath: 'dateEnd')]
    protected DateTime $dateStart;

    #[ORM\ManyToOne(targetEntity: Attachment::class, inversedBy: 'noteInternes', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'fond_id')]
    private ?Attachment $attachment = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[UploadableField(filename: 'fond', path: 'memo/fond', slug: 'title')]
    private mixed $file;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'memo', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $paragraphs;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    #[Assert\NotBlank]
    private string $title;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'noteInternes', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'refuser_id', nullable: false)]
    private ?UserInterface $user = null;

    public function __construct()
    {
        $this->dateStart  = new DateTime();
        $this->dateEnd    = new DateTime();
        $this->paragraphs = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addParagraph(Paragraph $paragraph): self
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs[] = $paragraph;
            $paragraph->setMemo($this);
        }

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getDateEnd(): ?DateTime
    {
        return $this->dateEnd;
    }

    public function getDateStart(): ?DateTime
    {
        return $this->dateStart;
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
     * @return Collection<int, Paragraph>
     */
    public function getParagraphs(): Collection
    {
        return $this->paragraphs;
    }

    public function getRefuser(): ?UserInterface
    {
        return $this->user;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function removeParagraph(Paragraph $paragraph): self
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getMemo() === $this) {
            $paragraph->setMemo(null);
        }

        return $this;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setDateEnd(?DateTime $dateTime): self
    {
        $this->dateEnd = $dateTime;

        return $this;
    }

    public function setDateStart(DateTime $dateTime): self
    {
        $this->dateStart = $dateTime;

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
