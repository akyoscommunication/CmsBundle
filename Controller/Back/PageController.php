<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\Page;
use Akyos\CmsBundle\Form\Type\Page\NewPageType;
use Akyos\CmsBundle\Form\Type\Page\PageType;
use Akyos\CmsBundle\Repository\PageRepository;
use Akyos\CmsBundle\Repository\SeoRepository;
use Akyos\CmsBundle\Service\CmsService;
use Akyos\CoreBundle\Form\Handler\CrudHandler;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Akyos\BuilderBundle\Entity\BuilderOptions;
use Akyos\BuilderBundle\AkyosBuilderBundle;

#[Route(path: '/admin/page', name: 'page_')]
#[IsGranted('pages')]
class PageController extends AbstractController
{
    /**
     * @param PageRepository $pageRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param CrudHandler $crudHandler
     * @return Response
     */
    #[Route(path: '/', name: 'index', methods: ['GET', 'POST'])]
    public function index(PageRepository $pageRepository, PaginatorInterface $paginator, Request $request, CrudHandler $crudHandler): Response
    {
        $query = $pageRepository->createQueryBuilder('a');
        if ($request->query->get('search')) {
            $query->andWhere('a.title LIKE :keyword OR a.slug LIKE :keyword')->setParameter('keyword', '%' . $request->query->get('search') . '%');
        }
        $els = $paginator->paginate($query->getQuery(), $request->query->getInt('page', 1), 12);
        $page = new Page();
        $page->setPublished(false);
        $page->setPosition($pageRepository->count([]));
        $newPageForm = $this->createForm(NewPageType::class, $page);
        if ($crudHandler->new($newPageForm, $request)) {
            return $this->redirectToRoute('page_edit', ['id' => $page->getId()]);
        }
        return $this->render('@AkyosCms/crud/index.html.twig', ['els' => $els, 'title' => 'Page', 'entity' => Page::class, 'view' => 'page', 'route' => 'page', 'header_route' => 'page', 'formModal' => $newPageForm->createView(), 'bundle' => 'CmsBundle', 'fields' => ['ID' => 'Id', 'Titre' => 'Title', 'Slug' => 'Slug', 'Position' => 'Position', 'Actif ?' => 'Published',],]);
    }

    /**
     * @param PageRepository $pageRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(PageRepository $pageRepository, EntityManagerInterface $entityManager): Response
    {
        $page = new Page();
        $page->setPublished(false);
        $page->setTitle("Nouvelle page");
        $page->setPosition($pageRepository->count([]));
        $entityManager->persist($page);
        $entityManager->flush();
        return $this->redirectToRoute('page_edit', ['id' => $page->getId()]);
    }

    /**
     * @param Request $request
     * @param Page $page
     * @param CmsService $cmsService
     * @param ContainerInterface $container
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Page $page, CmsService $cmsService, ContainerInterface $container, EntityManagerInterface $entityManager): Response
    {
        $entity = get_class($page);
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);
        $classBuilder = AkyosBuilderBundle::class;
        $classBuilderOption = BuilderOptions::class;
        if ($cmsService->checkIfBundleEnable($classBuilder, $classBuilderOption, $entity) && !$form->isSubmitted()) {
            $container->get('render.builder')->initCloneComponents($entity, $page->getId());
        }
        if ($form->isSubmitted() && $form->isValid()) {
            if ($cmsService->checkIfBundleEnable($classBuilder, $classBuilderOption, $entity)) {
                $container->get('render.builder')->tempToProd($entity, $page->getId());
            }
            $entityManager->flush();

            return $this->redirect($request->getUri());
        }
        if ($form->isSubmitted() && !($form->isValid())) {
            throw $this->createNotFoundException("Formulaire invalide.");
        }
        return $this->render('@AkyosCms/crud/edit.html.twig', ['el' => $page, 'title' => '"' . $page->getTitle() . '"', 'entity' => $entity, 'route' => 'page', 'header_route' => 'page_index', 'parameters' => ['slug' => $page->getSlug(),], 'view' => 'page', 'form' => $form->createView(),]);
    }

    /**
     * @param Request $request
     * @param Page $page
     * @param PageRepository $pageRepository
     * @param SeoRepository $seoRepository
     * @param CmsService $cmsService
     * @param ContainerInterface $container
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, Page $page, PageRepository $pageRepository, SeoRepository $seoRepository, CmsService $cmsService, ContainerInterface $container, EntityManagerInterface $entityManager): Response
    {
        $entity = get_class($page);
        if ($this->isCsrfTokenValid('delete' . $page->getId(), $request->request->get('_token'))) {
            $classBuilder = AkyosBuilderBundle::class;
            $classBuilderOption = BuilderOptions::class;
            if ($cmsService->checkIfBundleEnable($classBuilder, $classBuilderOption, $entity)) {
                $container->get('render.builder')->onDeleteEntity($entity, $page->getId());
            }

            $seo = $seoRepository->findOneBy(['type' => $entity, 'typeId' => $page->getId()]);
            if ($seo) {
                $entityManager->remove($seo);
            }
            $entityManager->remove($page);
            $entityManager->flush();

            $position = 0;
            foreach ($pageRepository->findBy([], ['position' => 'ASC']) as $el) {
                $el->setPosition($position);
                $position++;
            }
            $entityManager->flush();
        }
        return $this->redirectToRoute('page_index');
    }
}
