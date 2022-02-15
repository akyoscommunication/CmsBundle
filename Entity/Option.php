<?php

namespace Akyos\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Translatable;
use JsonException;

/**
 * @ORM\Entity(repositoryClass="Akyos\CmsBundle\Repository\OptionRepository")
 * @Orm\Table(name="`option`")
 */
class Option implements Translatable
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
	 * @Gedmo\Slug(fields={"title"})
	 * @Gedmo\Translatable
	 * @ORM\Column(type="string", length=255)
	 */
	private $slug;
	
	/**
	 * @ORM\Column(type="string", length=999999999999999999, nullable=true)
	 * @Gedmo\Translatable
	 */
	private $value;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $type;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Akyos\CmsBundle\Entity\OptionCategory", inversedBy="options")
	 */
	private $optionCategory;
	
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
	
	public function getValue()
	{
        try {
            $testJson = json_decode($this->value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return $this->value;
        }
        if (json_last_error() === JSON_ERROR_NONE) {
			return $testJson;
		}
		
		return $this->value;
	}
	
	public function setValue($value): self
	{
		if (is_array($value)) {
			$value = json_encode($value, JSON_THROW_ON_ERROR);
		}
		$this->value = $value;
		
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
	
	public function getOptionCategory(): ?OptionCategory
	{
		return $this->optionCategory;
	}
	
	public function setOptionCategory(?OptionCategory $optionCategory): self
	{
		$this->optionCategory = $optionCategory;
		
		return $this;
	}
}
