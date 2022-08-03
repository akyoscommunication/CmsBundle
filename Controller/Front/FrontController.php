<?php

namespace Akyos\CmsBundle\Controller\Front;

use Akyos\BuilderBundle\AkyosBuilderBundle;
use Akyos\BuilderBundle\Entity\BuilderOptions;
use Akyos\BuilderBundle\Entity\Component;
use Akyos\CmsBundle\Entity\Page;
use Akyos\CmsBundle\Repository\CmsOptionsRepository;
use Akyos\CmsBundle\Repository\PageRepository;
use Akyos\CmsBundle\Repository\SeoRepository;
use Akyos\CmsBundle\Service\CmsService;
use Akyos\CmsBundle\Service\FrontControllerService;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Translation;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class FrontController extends AbstractController
{
    /**
     * @param CmsOptionsRepository $cmsOptionsrepository
     * @param PageRepository $pageRepository
     * @param SeoRepository $seoRepository
     * @param Environment $environment
     * @param EntityManagerInterface $entityManager
     * @param CmsService $cmsService
     * @return Response
     */
    #[Route(path: '/', name: 'home', methods: ['GET', 'POST'])]
    public function home(CmsOptionsRepository $cmsOptionsrepository, PageRepository $pageRepository, SeoRepository $seoRepository, Environment $environment, EntityManagerInterface $entityManager, CmsService $cmsService): Response
    {
        // FIND HOMEPAGE
        $entity = Page::class;
        $cmsOptions = $cmsOptionsrepository->findAll();
        $homePage = $cmsOptions ? $cmsOptions[0]->getHomepage() : $pageRepository->findOneBy([], ['position' => "ASC"]);
        if (!$homePage) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Accueil )");
        }
        // GET COMPONENTS OR CONTENT
        $components = null;
        if ($cmsService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
            $components = $entityManager->getRepository(Component::class)->findBy(['type' => $entity, 'typeId' => $homePage->getId(), 'isTemp' => false, 'parentComponent' => null], ['position' => 'ASC']);
        }
        // GET TEMPLATE
        $view = $homePage->getTemplate() ? 'home/' . $homePage->getTemplate() . '.html.twig' : '@AkyosCms/front/content.html.twig';
        $environment->addGlobal('global_page', $homePage);
        // RENDER
        return $this->render($view, ['seo' => $seoRepository->findOneBy(['type' => $entity, 'typeId' => $homePage->getId()]), 'page' => $homePage, 'components' => $components, 'content' => $homePage->getContent(), 'slug' => 'accueil']);
    }

    /**
     * @param string $slug
     * @param FrontControllerService $frontControllerService
     * @return Response
     */
    #[Route(path: '/page_preview/{slug}', name: 'page_preview', methods: ['GET', 'POST'])]
    public function pagePreview(string $slug, FrontControllerService $frontControllerService): Response
    {
        return new Response($frontControllerService->pageAndPreview($slug, 'page_preview'));
    }

    /**
     * @param string $slug
     * @param FrontControllerService $frontControllerService
     * @return Response
     */
    #[Route(path: '/{slug}', methods: ['GET', 'POST'], requirements: ['slug' => '^(?!admin\/|_profiler\/|_wdt\/|app\/|recaptcha\/|page_preview\/|archive\/|details\/|details_preview\/|categorie\/|tag\/|file-manager\/|secured_files\/|en\/).+'], priority: -1, name: 'page')]
    public function page(string $slug, FrontControllerService $frontControllerService): Response
    {
        return new Response($frontControllerService->pageAndPreview($slug, 'page'));
    }

    /**
     * @param Filesystem $filesystem
     * @param string $entitySlug
     * @param CmsService $cmsService
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     * @return Response
     */
    #[Route(path: '/archive/{entitySlug}', name: 'archive', methods: ['GET', 'POST'])]
    public function archive(Filesystem $filesystem, string $entitySlug, CmsService $cmsService, Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager, KernelInterface $kernel): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        [$entityFullName, $entity] = $cmsService->getEntityAndFullString($entitySlug);
        if (!$entityFullName || !$entity) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Archive )");
        }
        if (!$cmsService->checkIfArchiveEnable($entityFullName)) {
            throw $this->createNotFoundException('La page archive n\'est pas activée pour cette entité ');
        }
        // GET ELEMENTS
        // Pour avoir la fonction de recherche, ajouter dans le repository de l'entité visée la méthode "search"
        if (method_exists($entityManager->getRepository($entityFullName), 'search')) {
            $elements = $paginator->paginate($entityManager->getRepository($entityFullName)->search($request->query->get('search')), $request->query->getInt('page', 1), 10);
        } else {
            $param = [];
            $order = [];

            if (property_exists($entityFullName, 'published')) {
                $param['published'] = true;
            }
            if (property_exists($entityFullName, 'publishedAt')) {
                $order['publishedAt'] = 'ASC';
            }

            $elements = $paginator->paginate($entityManager->getRepository($entityFullName)->findBy($param, $order), $request->query->getInt('page', 1), 10);
        }
        if (!$elements) {
            throw $this->createNotFoundException('Aucun élément pour cette entité! ');
        }
        // GET TEMPLATE
        $view = $filesystem->exists($kernel->getProjectDir() . '/templates/' . $entity . '/archive.html.twig') ? "/${entity}/archive.html.twig" : '@AkyosCms/front/archive.html.twig';
        // RENDER
        return $this->render($view, ['elements' => $elements, 'entity' => $entity, 'slug' => $entitySlug]);
    }

    /**
     * @param string $entitySlug
     * @param string $slug
     * @param FrontControllerService $frontControllerService
     * @return Response
     */
    #[Route(path: '/details_preview/{entitySlug}/{slug}', name: 'single_preview', methods: ['GET', 'POST'])]
    public function singlePreview(string $entitySlug, string $slug, FrontControllerService $frontControllerService): Response
    {
        return new Response($frontControllerService->singleAndPreview($entitySlug, $slug, 'single_preview'));
    }

    /**
     * @param string $entitySlug
     * @param string $slug
     * @param FrontControllerService $frontControllerService
     * @return Response
     */
    #[Route(path: '/details/{entitySlug}/{slug}', name: 'single', methods: ['GET', 'POST'])]
    public function single(string $entitySlug, string $slug, FrontControllerService $frontControllerService): Response
    {
        return new Response($frontControllerService->singleAndPreview($entitySlug, $slug, 'single'));
    }

    /**
     * @param Filesystem $filesystem
     * @param string $entitySlug
     * @param string $category
     * @param CmsService $cmsService
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     * @return Response
     */
    #[Route(path: '/categorie/{entitySlug}/{category}', name: 'taxonomy', methods: ['GET', 'POST'])]
    public function category(Filesystem $filesystem, string $entitySlug, string $category, CmsService $cmsService, Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager, KernelInterface $kernel): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        $meta = $entityManager->getMetadataFactory()->getAllMetadata();
        [$entityFullName, $entity] = $cmsService->getEntityAndFullString($entitySlug);
        if (!$entityFullName || !$entity) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Catégorie )");
        }
        // GET CATEGORY FULLNAME FROM ENTITY SLUG
        $categoryFullName = null;
        foreach ($meta as $m) {
            if (preg_match('/\x5c' . $entity . 'Category$/i', $m->getName())) {
                $categoryFullName = $m->getName();
            }
        }
        if (!$categoryFullName) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Catégorie )");
        }
        // FIND ELEMENTS FROM CATEGORY OBJECT
        $categoryObject = $entityManager->getRepository($categoryFullName)->findOneBy(['slug' => $category]) ?? (!$entityManager->getMetadataFactory()->isTransient(Translation::class) ? $entityManager->getRepository(Translation::class)->findObjectByTranslatedField('slug', $category, $categoryFullName) : null);
        if (!$categoryObject) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Catégorie )");
        }
        // GET ELEMENTS
        // Pour avoir la fonction de recherche, ajouter dans le repository de l'entité visée la méthode "searchByCategory"
        if (method_exists($entityManager->getRepository($entityFullName), 'searchByCategory')) {
            $elements = $paginator->paginate($entityManager->getRepository($entityFullName)->searchByCategory($categoryObject, $request->query->get('search')), $request->query->getInt('page', 1), 10);
        } else {
            $qb = $entityManager->getRepository($entityFullName)->createQueryBuilder('a');
            $params = [];

            if (property_exists($entityFullName, 'postCategories')) {
                $qb->innerJoin('a.postCategories', 'apc')->andWhere($qb->expr()->eq('apc', ':cat'));
                $params['cat'] = $categoryObject;
            }
            if (property_exists($entityFullName, 'published')) {
                $qb->andWhere($qb->expr()->eq('a.published', true));
            }
            if (property_exists($entityFullName, 'publishedAt')) {
                $qb->orderBy('a.publishedAt', 'ASC');
            }

            $elements = $paginator->paginate($qb->setParameters($params)->getQuery(), $request->query->getInt('page', 1), 10);
        }
        // GET TEMPLATE
        $view = $filesystem->exists($kernel->getProjectDir() . '/templates/' . $entity . '/category.html.twig') ? "${entity}/category.html.twig" : '@AkyosCms/front/category.html.twig';
        // RENDER
        return $this->render($view, ['elements' => $elements, 'entity' => $entity, 'slug' => $category, 'category' => $categoryObject]);
    }

    /**
     * @param Filesystem $filesystem
     * @param string $entitySlug
     * @param string $tag
     * @param CmsService $cmsService
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     * @return Response
     */
    #[Route(path: '/tag/{entitySlug}/{tag}', name: 'tag', methods: ['GET', 'POST'])]
    public function tag(Filesystem $filesystem, string $entitySlug, string $tag, CmsService $cmsService, EntityManagerInterface $entityManager, KernelInterface $kernel): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        $meta = $entityManager->getMetadataFactory()->getAllMetadata();
        [$entityFullName, $entity] = $cmsService->getEntityAndFullString($entitySlug);
        if (!$entityFullName || !$entity) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Étiquette )");
        }
        $parentEntity = str_replace('Tag', '', $entity);
        // GET TAG FULLNAME FROM ENTITY SLUG
        $tagFullName = null;
        foreach ($meta as $m) {
            if (preg_match('/' . $entity . '$/i', $m->getName())) {
                $tagFullName = $m->getName();
            }
        }
        if (!$tagFullName) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Étiquette )");
        }
        // FIND ELEMENTS FROM TAG OBJECT
        $tagObject = $entityManager->getRepository($tagFullName)->findOneBy(['slug' => $tag]) ?? (!$entityManager->getMetadataFactory()->isTransient(Translation::class) ? $entityManager->getRepository(Translation::class)->findObjectByTranslatedField('slug', $tag, $tagFullName) : null);
        if (!$tagObject) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Étiquette )");
        }
        if (str_ends_with($entity, "y")) {
            $getter = 'get' . ucfirst(substr($parentEntity, 0, -1)) . 'ies';
        } else {
            $getter = 'get' . ucfirst($parentEntity) . 's';
        }
        $elements = $tagObject->$getter();
        // GET TEMPLATE
        $view = $filesystem->exists($kernel->getProjectDir() . '/templates/' . $parentEntity . '/tag.html.twig') ? "${parentEntity}/tag.html.twig" : '@AkyosCms/front/tag.html.twig';
        // RENDER
        return $this->render($view, ['elements' => $elements, 'entity' => $entity, 'slug' => $tag, 'tag' => $tagObject]);
    }
}
