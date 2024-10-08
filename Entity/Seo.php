<?php

namespace Akyos\CmsBundle\Entity;

use Akyos\CmsBundle\Repository\SeoRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Translatable;

#[ORM\Entity(repositoryClass: SeoRepository::class)]
class Seo implements Translatable
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Gedmo\Translatable]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $metaTitle;

    #[Gedmo\Translatable]
    #[ORM\Column(type: 'text', nullable: true)]
    private $metaDescription;

    #[Gedmo\Translatable]
    #[ORM\Column(type: 'boolean')]
    private $noIndex;

    #[Gedmo\Translatable]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $metaRobots;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $type;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $typeId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): ?self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): ?self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getNoIndex(): ?bool
    {
        return $this->noIndex;
    }

    public function setNoIndex(?bool $noIndex): ?self
    {
        $this->noIndex = $noIndex;

        return $this;
    }

    public function getMetaRobots(): ?string
    {
        return $this->metaRobots;
    }

    public function setMetaRobots(?string $metaRobots): ?self
    {
        $this->metaRobots = $metaRobots;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): ?self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    public function setTypeId(?int $typeId): ?self
    {
        $this->typeId = $typeId;

        return $this;
    }
}
