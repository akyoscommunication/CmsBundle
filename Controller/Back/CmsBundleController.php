<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\MenuArea;
use Gedmo\Translatable\Entity\Translation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", name="cms_")
 */
class CmsBundleController extends AbstractController
{
	/**
	 * @Route("/", name="index")
	 */
	public function index(): Response
	{
		return $this->render('@AkyosCms/cms_bundle/index.html.twig', [
			'title' => 'Tableau de Bord',
		]);
	}
	
	public function renderMenu($menu, $page): string
	{
		$menuArea = $this->getDoctrine()->getRepository(MenuArea::class)->findOneBy(['slug' => $menu]) ??
			(!$this->getDoctrine()->getManager()->getMetadataFactory()->isTransient(Translation::class)
				? $this->getDoctrine()->getRepository(Translation::class)->findObjectByTranslatedField('slug', $menu, MenuArea::class) : null);
		return $this->renderView('@AkyosCms/menu/render.html.twig', [
			'menu' => $menuArea,
			'currentPage' => $page,
		]);
	}
}
