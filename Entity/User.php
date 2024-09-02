<?php

namespace Akyos\CmsBundle\Entity;

use Akyos\CmsBundle\Repository\UserRepository;
use Akyos\CoreBundle\Entity\BaseUser;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User extends BaseUser
{
    public const ROLES = ['Visiteur' => 'ROLE_USER', 'Community Manager' => 'ROLE_CM', 'Administrateur' => 'ROLE_ADMIN', 'Super Admin' => 'ROLE_SUPER_ADMIN', 'Akyos' => 'ROLE_AKYOS'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
