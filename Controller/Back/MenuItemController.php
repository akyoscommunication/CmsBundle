<?php


namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\MenuItem;
use Akyos\CmsBundle\Form\MenuItemType;
use Akyos\CmsBundle\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("admin/menu/item", name="menu_item_")
 * @IsGranted("elements-du-menu")
 */
class MenuItemController extends AbstractController
{
	/**
	 * @Route("/{id}/edit/{menu}", name="edit", methods={"GET","POST"})
	 * @param Request $request
	 * @param MenuItem $menuItem
	 * @param $menu
	 * @param MenuRepository $menuRepository
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function edit(Request $request, MenuItem $menuItem, $menu, MenuRepository $menuRepository, EntityManagerInterface $entityManager): Response
	{
		$menu = $menuRepository->find($menu);
		$form = $this->createForm(MenuItemType::class, $menuItem, ['menu' => $menu]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$entityManager->flush();

			return new Response('valid');
		}

		return $this->render('@AkyosCms/menu_item/edit.html.twig', [
			'menu_item' => $menuItem,
			'form' => $form->createView(),
		]);
	}
	
	/**
	 * @Route("/{id}", name="delete", methods={"DELETE"})
	 * @param Request $request
	 * @param MenuItem $menuItem
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function delete(Request $request, MenuItem $menuItem, EntityManagerInterface $entityManager): Response
	{
		if ($this->isCsrfTokenValid('delete' . $menuItem->getId(), $request->request->get('_token'))) {
			$entityManager->remove($menuItem);
			$entityManager->flush();
		}

		return $this->redirect($request->headers->get('referer'));
	}
}
