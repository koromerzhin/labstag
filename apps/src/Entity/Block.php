<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Block\Breadcrumb;
use Labstag\Entity\Block\Custom;
use Labstag\Entity\Block\Flashbag;
use Labstag\Entity\Block\Footer;
use Labstag\Entity\Block\Header;
use Labstag\Entity\Block\Html;
use Labstag\Entity\Block\Navbar;
use Labstag\Entity\Block\Paragraph;
use Labstag\Interfaces\EntityBlockInterface;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Repository\BlockRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: BlockRepository::class)]
class Block implements EntityTrashInterface
{
    use SoftDeleteableEntity;

    #[ORM\OneToMany(targetEntity: Breadcrumb::class, mappedBy: 'block', cascade: ['persist'], orphanRemoval: true)]
    private Collection $breadcrumbs;

    #[ORM\OneToMany(targetEntity: Custom::class, mappedBy: 'block', cascade: ['persist'], orphanRemoval: true)]
    private Collection $customs;

    #[ORM\OneToMany(targetEntity: Flashbag::class, mappedBy: 'block', cascade: ['persist'], orphanRemoval: true)]
    private Collection $flashbags;

    #[ORM\OneToMany(targetEntity: Footer::class, mappedBy: 'block', cascade: ['persist'], orphanRemoval: true)]
    private Collection $footers;

    #[ORM\ManyToMany(targetEntity: Groupe::class, inversedBy: 'blocks')]
    private Collection $groupes;

    #[ORM\OneToMany(targetEntity: Header::class, mappedBy: 'block', cascade: ['persist'], orphanRemoval: true)]
    private Collection $headers;

    #[ORM\OneToMany(targetEntity: Html::class, mappedBy: 'block', cascade: ['persist'], orphanRemoval: true)]
    private Collection $htmls;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Navbar::class, mappedBy: 'block', cascade: ['persist'], orphanRemoval: true)]
    private Collection $menu;

    #[ORM\ManyToMany(targetEntity: Page::class, inversedBy: 'blocks')]
    private Collection $notinpages;

    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'block', cascade: ['persist'], orphanRemoval: true)]
    private Collection $paragraphs;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $region = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $type = null;

    public function __construct()
    {
        $this->headers     = new ArrayCollection();
        $this->htmls       = new ArrayCollection();
        $this->footers     = new ArrayCollection();
        $this->paragraphs  = new ArrayCollection();
        $this->breadcrumbs = new ArrayCollection();
        $this->menu        = new ArrayCollection();
        $this->flashbags   = new ArrayCollection();
        $this->customs     = new ArrayCollection();
        $this->groupes     = new ArrayCollection();
        $this->notinpages  = new ArrayCollection();
    }

    public function addBreadcrumb(Breadcrumb $breadcrumb): self
    {
        if (!$this->breadcrumbs->contains($breadcrumb)) {
            $this->breadcrumbs[] = $breadcrumb;
            $breadcrumb->setBlock($this);
        }

        return $this;
    }

    public function addCustom(Custom $custom): self
    {
        if (!$this->customs->contains($custom)) {
            $this->customs[] = $custom;
            $custom->setBlock($this);
        }

        return $this;
    }

    public function addFlashbag(Flashbag $flashbag): self
    {
        if (!$this->flashbags->contains($flashbag)) {
            $this->flashbags[] = $flashbag;
            $flashbag->setBlock($this);
        }

        return $this;
    }

    public function addFooter(Footer $footer): self
    {
        if (!$this->footers->contains($footer)) {
            $this->footers[] = $footer;
            $footer->setBlock($this);
        }

        return $this;
    }

    public function addGroupe(Groupe $groupe): self
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes->add($groupe);
        }

        return $this;
    }

    public function addHeader(Header $header): self
    {
        if (!$this->headers->contains($header)) {
            $this->headers[] = $header;
            $header->setBlock($this);
        }

        return $this;
    }

    public function addHtml(Html $html): self
    {
        if (!$this->htmls->contains($html)) {
            $this->htmls[] = $html;
            $html->setBlock($this);
        }

        return $this;
    }

    public function addMenu(Navbar $navbar): self
    {
        if (!$this->menu->contains($navbar)) {
            $this->menu[] = $navbar;
            $navbar->setBlock($this);
        }

        return $this;
    }

    public function addNotinpage(Page $page): self
    {
        if (!$this->notinpages->contains($page)) {
            $this->notinpages->add($page);
        }

        return $this;
    }

    public function addParagraph(Paragraph $paragraph): self
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs[] = $paragraph;
            $paragraph->setBlock($this);
        }

        return $this;
    }

    public function getBreadcrumbs(): Collection
    {
        return $this->breadcrumbs;
    }

    public function getCustoms(): Collection
    {
        return $this->customs;
    }

    public function getFlashbags(): Collection
    {
        return $this->flashbags;
    }

    public function getFooters(): Collection
    {
        return $this->footers;
    }

    /**
     * @return Collection<int, Groupe>
     */
    public function getGroupes(): Collection
    {
        return $this->groupes;
    }

    public function getHeaders(): Collection
    {
        return $this->headers;
    }

    public function getHtmls(): Collection
    {
        return $this->htmls;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMenu(): Collection
    {
        return $this->menu;
    }

    /**
     * @return Collection<int, Page>
     */
    public function getNotinpages(): Collection
    {
        return $this->notinpages;
    }

    public function getParagraphs(): Collection
    {
        return $this->paragraphs;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function removeBreadcrumb(Breadcrumb $breadcrumb): self
    {
        $this->removeElementBlock($this->breadcrumbs, $breadcrumb);

        return $this;
    }

    public function removeCustom(Custom $custom): self
    {
        $this->removeElementBlock($this->customs, $custom);

        return $this;
    }

    public function removeFlashbag(Flashbag $flashbag): self
    {
        $this->removeElementBlock($this->flashbags, $flashbag);

        return $this;
    }

    public function removeFooter(Footer $footer): self
    {
        $this->removeElementBlock($this->footers, $footer);

        return $this;
    }

    public function removeGroupe(Groupe $groupe): self
    {
        $this->groupes->removeElement($groupe);

        return $this;
    }

    public function removeHeader(Header $header): self
    {
        $this->removeElementBlock($this->headers, $header);

        return $this;
    }

    public function removeHtml(Html $html): self
    {
        $this->removeElementBlock($this->htmls, $html);

        return $this;
    }

    public function removeMenu(Navbar $navbar): self
    {
        $this->removeElementBlock($this->menu, $navbar);

        return $this;
    }

    public function removeNotinpage(Page $page): self
    {
        $this->notinpages->removeElement($page);

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): self
    {
        $this->removeElementBlock($this->paragraphs, $paragraph);

        return $this;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    private function removeElementBlock(
        Collection $element,
        EntityBlockInterface $entityBlock
    ): void
    {
        if ($element->removeElement($entityBlock) && $entityBlock->getBlock() === $this) {
            $entityBlock->setBlock(null);
        }
    }
}
