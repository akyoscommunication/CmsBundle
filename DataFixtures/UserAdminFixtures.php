<?php

namespace Akyos\CmsBundle\DataFixtures;

use Akyos\CmsBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserAdminFixtures extends Fixture
{
	private UserPasswordHasherInterface $passwordHasher;

	public function __construct(UserPasswordHasherInterface $passwordHasher)
	{
		$this->passwordHasher = $passwordHasher;
	}

	public function load(ObjectManager $manager)
	{
		$user = new User();
		$user
			->setEmail("admin@akyos.fr")
			->setPassword($this->passwordHasher->hashPassword(
				$user,
				'root'
			))
			->setRoles(["ROLE_AKYOS"]);

		$manager->persist($user);
		$manager->flush();
	}
}
