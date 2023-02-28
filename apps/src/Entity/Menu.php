<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Block\Navbar;
use Labstag\Repository\MenuRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MenuRepository::class)
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Menu implements Stringable
{
    use SoftDeleteableEntity;

    /**
     * @ORM\OneToMany(targetEntity=Menu::class, mappedBy="parent", cascade={"persist"}, orphanRemoval=true)
     *
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $children;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $clef;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    protected array $data = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icon;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Menu::class, inversedBy="children", cascade={"persist"})
     *
     * @ORM\JoinColumn(
     *     name="parent_id",
     *     referencedColumnName="id",
     *     onDelete="SET NULL"
     * )
     */
    protected ?Menu $parent = null;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotNull
     */
    protected int $position = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $separateur = false;

    /**
     * @ORM\OneToMany(targetEntity=Navbar::class, mappedBy="menu", cascade={"persist"}, orphanRemoval=true)
     */
    private $navbars;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->navbars = new ArrayCollection();
    }

    public function __toString(): string
    {
        return implode(
            ' ',
            [
                $this->getId(),
                '-',
                '('.$this->getClef().')',
                '-',
                $this->getName(),
            ]
        );
    }

    public function addChild(Menu $menu): self
    {
        if (!$this->children->contains($menu)) {
            $this->children[] = $menu;
            $menu->setParent($this);
        }

        return $this;
    }

    public function addNavbar(Navbar $navbar): self
    {
        if (!$this->navbars->contains($navbar)) {
            $this->navbars[] = $navbar;
            $navbar->setMenu($this);
        }

        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getClef(): ?string
    {
        return $this->clef;
    }

    public function getData(): ?array
    {
        return is_array($this->data) ? $this->data : [];
    }

    public function getIcon(): ?string
    {
        return $this->icon;
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
     * @return Collection<int, Navbar>
     */
    public function getNavbars(): Collection
    {
        return $this->navbars;
    }

    public function getParent(): ?Menu
    {
        return $this->parent;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getSeparateur(): ?bool
    {
        return $this->separateur;
    }

    public function isSeparateur(): ?bool
    {
        return $this->separateur;
    }

    public function removeChild(Menu $menu): self
    {
        // set the owning side to null (unless already changed)
        if ($this->children->removeElement($menu) && $menu->getParent() === $this) {
            $menu->setParent(null);
        }

        return $this;
    }

    public function removeNavbar(Navbar $navbar): self
    {
        // set the owning side to null (unless already changed)
        if ($this->navbars->removeElement($navbar) && $navbar->getMenu() === $this) {
            $navbar->setMenu(null);
        }

        return $this;
    }

    public function setClef(?string $clef): self
    {
        $this->clef = $clef;

        return $this;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setParent(?Menu $menu): void
    {
        $this->parent = $menu;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function setSeparateur(bool $separateur): self
    {
        $this->separateur = $separateur;

        return $this;
    }
}
