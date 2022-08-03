<?php

namespace Akyos\CmsBundle\Entity;

use Akyos\CmsBundle\Annotations\SlugRedirect;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Translatable;
use Akyos\CmsBundle\Repository\PageRepository;

#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page implements Translatable
{
    use TimestampableEntity;

    public const ENTITY_SLUG = "pages";

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
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     * @SlugRedirect
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'string', length: 255)]
    private $slug;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'boolean')]
    private $published;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'integer')]
    private $position;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'string', length: 999999999999999999, nullable: true)]
    private $content;

    /**
     * @Gedmo\Translatable
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $thumbnail;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $publishedAt;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    public function __toString(): string
    {
        return (string)$this->title;
    }

    public function getPublishedAt(): ?DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }
}
