<?php

namespace Akyos\CmsBundle\Entity;

use Akyos\CmsBundle\Repository\CustomFieldValueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomFieldValueRepository::class)]
class CustomFieldValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: CustomField::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $customField;

    #[ORM\Column(type: 'integer')]
    private $objectId;

    #[ORM\Column(type: 'string', length: 9999, nullable: true)]
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomField(): ?CustomField
    {
        return $this->customField;
    }

    public function setCustomField(?CustomField $customField): self
    {
        $this->customField = $customField;

        return $this;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    public function setObjectId(int $objectId): self
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    // Dont type the $value param, it's string in database but we need to set arrays when formType is a select
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }
}
