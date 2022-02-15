<?php

namespace Akyos\CmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass="Akyos\CmsBundle\Repository\MenuItemRepository")
 */
class MenuItem implements Translatable
{
	use TimestampableEntity;
	
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 * @Gedmo\Translatable
	 */
	private $title;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Translatable
	 */
	private $url;
	
	/**
	 * @ORM\Column(type="boolean")
	 * @Gedmo\Translatable
	 */
	private $isParent;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Translatable
	 */
	private $type;
	
	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @Gedmo\Translatable
	 */
	private $idType;
	
	/**
	 * @ORM\Column(type="boolean")
	 * @Gedmo\Translatable
	 */
	private $isList;
	
	/**
	 * @ORM\Column(type="integer")
	 * @Gedmo\Translatable
	 */
	private $position;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Akyos\CmsBundle\Entity\Menu", inversedBy="menuItems")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $menu;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Akyos\CmsBundle\Entity\MenuItem", inversedBy="menuItemsChilds")
	 */
	private $menuItemParent;
	
	/**
	 * @ORM\OneToMany(targetEntity="Akyos\CmsBundle\Entity\MenuItem", mappedBy="menuItemParent", orphanRemoval=true)
	 * @OrderBy({"position" = "ASC"})
	 */
	private $menuItemsChilds;
	
	/**
	 * @Gedmo\Slug(fields={"title"})
	 * @Gedmo\Translatable
	 * @ORM\Column(type="string", length=255)
	 */
	private $slug;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Translatable
	 */
	private $target;
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @Gedmo\Translatable
	 */
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
	
	public function getMenuItemParent(): ?self
	{
		return $this->menuItemParent;
	}
	
	public function setMenuItemParent(?self $menuItemParent): self
	{
		$this->menuItemParent = $menuItemParent;
		
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
