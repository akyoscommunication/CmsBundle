<?php

namespace Akyos\CmsBundle\DataFixtures;

use Akyos\CmsBundle\Entity\CmsOptions;
use Akyos\CmsBundle\Entity\Menu;
use Akyos\CmsBundle\Entity\MenuArea;
use Akyos\CmsBundle\Entity\Page;
use Akyos\CmsBundle\Entity\Seo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CmsInstallFixtures extends Fixture
{
	public function load(ObjectManager $manager)
	{
		$homepage = new Page();
		$homepage
			->setTitle('Accueil')
			->setSlug('accueil')
			->setPublished(true)
			->setPosition(0);
		$manager->persist($homepage);
		$manager->flush();

		$seo = new Seo();
		$seo
			->setNoIndex(0)
			->setType('Page')
			->setTypeId($homepage->getId());
		$manager->persist($seo);
		$manager->flush();

		$menuArea = new MenuArea();
		$menuArea
			->setName('Menu principal')
			->setSlug('menu-principal')
			->setDescription('Menu présent sur toutes les pages, dans le header');
		$manager->persist($menuArea);
		$manager->flush();

		$menu = new Menu();
		$menu
			->setTitle('Menu principal')
			->setSlug('menu-principal')
			->setMenuArea($menuArea);
		$manager->persist($menu);
		$manager->flush();

		$cmsOptions = new CmsOptions();
		$cmsOptions
			->setHomepage($homepage)
			->setSiteTitle('Nouveau site')
			->setBackMainColor('#000000')
			->setHasSeoEntities([Page::class])
			->setAgencyLink('https://akyos.com')
			->setAgencyName('Akyos Communication');
		$manager->persist($cmsOptions);
		$manager->flush();
	}
}
