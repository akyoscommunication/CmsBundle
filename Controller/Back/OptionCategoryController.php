<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\OptionCategory;
use Akyos\CmsBundle\Form\OptionCategoryType;
use Akyos\CmsBundle\Repository\OptionCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/site_option/category', name: 'option_category_')]
#[IsGranted('categorie-doptions-du-site')]
class OptionCategoryController extends AbstractController
{
    /**
     * @param OptionCategoryRepository $optionCategoryRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(OptionCategoryRepository $optionCategoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $optionCategoryRepository->createQueryBuilder('a');
        if ($request->query->get('search')) {
            $query->andWhere('a.title LIKE :keyword OR a.slug LIKE :keyword')->setParameter('keyword', '%' . $request->query->get('search') . '%');
        }
        $els = $paginator->paginate($query->getQuery(), $request->query->getInt('page', 1), 12);
        return $this->render('@AkyosCms/crud/index.html.twig', ['els' => $els, 'title' => 'Catégorie d\'options', 'entity' => 'Option', 'route' => 'option_category', 'fields' => ['ID' => 'Id', 'Slug' => 'Slug', 'Title' => 'Title',],]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $optionCategory = new OptionCategory();
        $form = $this->createForm(OptionCategoryType::class, $optionCategory);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($optionCategory);
            $entityManager->flush();

            return $this->redirectToRoute('option_category_index');
        }
        return $this->render('@AkyosCms/crud/new.html.twig', ['el' => $optionCategory, 'title' => 'Catégorie d\'options', 'entity' => 'Option', 'route' => 'option_category', 'fields' => ['ID' => 'Id', 'Slug' => 'Slug', 'Title' => 'Title',], 'form' => $form->createView(),]);
    }

    /**
     * @param Request $request
     * @param OptionCategory $optionCategory
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OptionCategory $optionCategory, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OptionCategoryType::class, $optionCategory);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('option_category_index');
        }
        return $this->render('@AkyosCms/crud/edit.html.twig', ['el' => $optionCategory, 'title' => 'Catégorie d\'options', 'entity' => 'Option', 'route' => 'option_category', 'fields' => ['Title' => 'Title', 'ID' => 'Id'], 'form' => $form->createView(),]);
    }

    /**
     * @param Request $request
     * @param OptionCategory $optionCategory
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, OptionCategory $optionCategory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $optionCategory->getId(), $request->request->get('_token'))) {
            $entityManager->remove($optionCategory);
            $entityManager->flush();
        }
        return $this->redirectToRoute('option_category_index');
    }
}
