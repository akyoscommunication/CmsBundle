<?php

namespace Akyos\CmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Translatable;
use Akyos\CmsBundle\Entity\MenuItem;
use Akyos\CmsBundle\Entity\Menu;
use Akyos\CmsBundle\Repository\MenuItemRepository;

#[ORM\Entity(repositoryClass: MenuItemRepository::class)]
class MenuItem implements Translatable
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $url;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'boolean')]
    private $isParent;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $type;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $idType;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'boolean')]
    private $isList;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'integer')]
    private $position;

    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'menuItems')]
    #[ORM\JoinColumn(nullable: false)]
    private $menu;

    #[ORM\ManyToOne(targetEntity: MenuItem::class, inversedBy: 'menuItemsChilds')]
    private $menuItemParent;

    #[ORM\OneToMany(targetEntity: MenuItem::class, mappedBy: 'menuItemParent', orphanRemoval: true)]
    #[OrderBy(['position' => 'ASC'])]
    private $menuItemsChilds;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'string', length: 255)]
    private $slug;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $target;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $isCategoryList;

    public function __construct()
    {
        $this->menuItemsChilds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getIsParent(): ?bool
    {
        return $this->isParent;
    }

    public function setIsParent(bool $isParent): self
    {
        $this->isParent = $isParent;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIdType(): ?int
    {
        return $this->idType;
    }

    public function setIdType(?int $idType): self
    {
        $this->idType = $idType;

        return $this;
    }

    public function getIsList(): ?bool
    {
        return $this->isList;
    }

    public function setIsList(bool $isList): self
    {
        $this->isList = $isList;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getMenuItemsChilds(): Collection
    {
        return $this->menuItemsChilds;
    }

    public function addMenuItemsChild(self $menuItemsChild): self
    {
        if (!$this->menuItemsChilds->contains($menuItemsChild)) {
            $this->menuItemsChilds[] = $menuItemsChild;
            $menuItemsChild->setMenuItemParent($this);
        }

        return $this;
    }

    public function removeMenuItemsChild(self $menuItemsChild): self
    {
        if ($this->menuItemsChilds->contains($menuItemsChild)) {
            $this->menuItemsChilds->removeElement($menuItemsChild);
            // set the owning side to null (unless already changed)
            if ($menuItemsChild->getMenuItemParent() === $this) {
                $menuItemsChild->setMenuItemParent(null);
            }
        }

        return $this;
    }

    public function getMenuItemParent(): ?self
    {
        return $this->menuItemParent;
    }

    public function setMenuItemParent(?self $menuItemParent): self
    {
        $this->menuItemParent = $menuItemParent;

        return $this;
    }

    public function __toString()
    {
        return $this->slug;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getIsCategoryList(): ?bool
    {
        return $this->isCategoryList;
    }

    public function setIsCategoryList(?bool $isCategoryList): self
    {
        $this->isCategoryList = $isCategoryList;

        return $this;
    }
}
