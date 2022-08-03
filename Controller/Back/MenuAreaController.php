<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\MenuArea;
use Akyos\CmsBundle\Form\MenuAreaType;
use Akyos\CmsBundle\Repository\MenuAreaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/menu/area', name: 'menu_area_')]
#[IsGranted('zones-de-menus')]
class MenuAreaController extends AbstractController
{
    /**
     * @param MenuAreaRepository $menuAreaRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(MenuAreaRepository $menuAreaRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $menuAreaRepository->createQueryBuilder('a');
        if ($request->query->get('search')) {
            $query->leftJoin('a.Menu', 'menu')->andWhere('a.name LIKE :keyword OR a.slug LIKE :keyword OR a.description LIKE :keyword OR menu.title LIKE :keyword')->setParameter('keyword', '%' . $request->query->get('search') . '%');
        }
        $els = $paginator->paginate($query->getQuery(), $request->query->getInt('page', 1), 12);
        return $this->render('@AkyosCms/crud/index.html.twig', ['els' => $els, 'title' => 'Zones de menu', 'entity' => 'MenuArea', 'route' => 'menu_area', 'fields' => ['ID' => 'Id', 'Nom' => 'Name', 'Slug' => 'Slug', 'Description' => 'Description', 'Menu' => 'Menu'],]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $menuArea = new MenuArea();
        $form = $this->createForm(MenuAreaType::class, $menuArea);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($menuArea);
            $entityManager->flush();

            return $this->redirectToRoute('menu_area_index');
        }
        return $this->render('@AkyosCms/crud/new.html.twig', ['el' => $menuArea, 'title' => 'Zone de menu', 'entity' => 'MenuArea', 'route' => 'menu_area', 'form' => $form->createView(),]);
    }

    /**
     * @param Request $request
     * @param MenuArea $menuArea
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MenuArea $menuArea, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MenuAreaType::class, $menuArea);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('menu_area_index');
        }
        return $this->render('@AkyosCms/crud/edit.html.twig', ['el' => $menuArea, 'title' => 'Zone de menu', 'entity' => 'MenuArea', 'route' => 'menu_area', 'form' => $form->createView(),]);
    }

    /**
     * @param Request $request
     * @param MenuArea $menuArea
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, MenuArea $menuArea, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $menuArea->getId(), $request->request->get('_token'))) {
            $menu = $menuArea->getMenu();
            if ($menu) {
                $menu->setMenuArea(null);
            }
            $entityManager->remove($menuArea);
            $entityManager->flush();
        }
        return $this->redirectToRoute('menu_area_index');
    }
}
