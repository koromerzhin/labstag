<?php

namespace Labstag\Entity\Paragraph\Post;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Entity\Paragraph;
use Labstag\Interfaces\EntityInterface;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Repository\Paragraph\Post\LibelleRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: LibelleRepository::class)]
#[ORM\Table(name: 'paragraph_post_libelle')]
#[ApiResource(routePrefix: '/paragraph/post')]
class Libelle implements Stringable, EntityParagraphInterface, EntityInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: Paragraph::class, inversedBy: 'postLibelles', cascade: ['persist'])]
    private ?Paragraph $paragraph = null;

    public function __toString(): string
    {
        /** @var Paragraph $paragraph */
        $paragraph = $this->getParagraph();

        return (string) $paragraph->getType();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getParagraph(): ?Paragraph
    {
        return $this->paragraph;
    }

    public function setParagraph(?Paragraph $paragraph): self
    {
        $this->paragraph = $paragraph;

        return $this;
    }
}
