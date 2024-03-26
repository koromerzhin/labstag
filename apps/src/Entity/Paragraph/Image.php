<?php

namespace Labstag\Entity\Paragraph;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Labstag\Annotation\Uploadable;
use Labstag\Annotation\UploadableField;
use Labstag\Entity\Attachment;
use Labstag\Entity\Paragraph;
use Labstag\Interfaces\EntityInterface;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Repository\Paragraph\ImageRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[Uploadable]
#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ORM\Table(name: 'paragraph_image')]
class Image implements EntityParagraphInterface, EntityInterface, Stringable
{

    #[ORM\ManyToOne(targetEntity: Attachment::class, inversedBy: 'paragraphImages', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'image_id', nullable: true, onDelete: 'SET NULL')]
    private ?Attachment $attachment = null;

    #[UploadableField(filename: 'image', path: 'paragraph/image', slug: 'title')]
    private mixed $file;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: Paragraph::class, inversedBy: 'images', cascade: ['persist'])]
    private ?Paragraph $paragraph = null;

    #[Gedmo\Slug(updatable: false, fields: ['title'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title = null;

    public function __toString(): string
    {
        /** @var Paragraph $paragraph */
        $paragraph = $this->getParagraph();

        return (string) $paragraph->getType();
    }

    public function getFile(): mixed
    {
        return $this->file;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getImage(): ?Attachment
    {
        return $this->attachment;
    }

    public function getParagraph(): ?Paragraph
    {
        return $this->paragraph;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setFile(mixed $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function setImage(?Attachment $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function setParagraph(?Paragraph $paragraph): self
    {
        $this->paragraph = $paragraph;

        return $this;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
