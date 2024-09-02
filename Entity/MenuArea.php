<?php

namespace Akyos\CmsBundle\Entity;

use Akyos\CmsBundle\Repository\MenuAreaRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Translatable;

#[ORM\Entity(repositoryClass: MenuAreaRepository::class)]
class MenuArea implements Translatable
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Gedmo\Translatable]
    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[Gedmo\Translatable]
    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(type: 'string', length: 255)]
    private $slug;

    #[ORM\OneToOne(targetEntity: Menu::class, inversedBy: 'menuArea', cascade: ['persist'])]
    private $Menu;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $description;

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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

    public function getMenu(): ?Menu
    {
        return $this->Menu;
    }

    public function setMenu(?Menu $Menu): self
    {
        $this->Menu = $Menu;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
