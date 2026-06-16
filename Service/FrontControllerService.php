<?php

namespace Akyos\CmsBundle\Service;

use Akyos\BuilderBundle\AkyosBuilderBundle;
use Akyos\BuilderBundle\Entity\BuilderOptions;
use Akyos\BuilderBundle\Entity\Component;
use Akyos\CmsBundle\Entity\Page;
use Akyos\CmsBundle\Entity\Redirect301;
use Akyos\CmsBundle\Entity\Seo;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class FrontControllerService
{
    private readonly EntityManagerInterface $em;

    private readonly RouterInterface $router;

    private readonly Filesystem $filesystem;

    private readonly KernelInterface $kernel;

    private readonly Environment $environment;

    private readonly AuthorizationCheckerInterface $checker;

    public function __construct(EntityManagerInterface $em, RouterInterface $router, Filesystem $filesystem, KernelInterface $kernel, Environment $environment, private readonly CmsService $cmsService, AuthorizationCheckerInterface $checker)
    {
        $this->em = $em;
        $this->router = $router;
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
        $this->environment = $environment;
        $this->checker = $checker;
    }

    /**
     * @param string $entitySlug
     * @param string $slug
     * @param string $route
     * @return string|RedirectResponse
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function singleAndPreview(string $entitySlug, string $slug, string $route)
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        [$entityFullName, $entity] = $this->cmsService->getEntityAndFullString($entitySlug);

        if (!$entityFullName || !$entity || !$this->cmsService->checkIfSingleEnable($entityFullName)) {
            throw new NotFoundHttpException("Cette page n'existe pas! ( Détail )");
        }

        $slug = str_ends_with($slug, "/") ? substr($slug, 0, -1) : $slug;

        // GET ELEMENT
        $element = $this->cmsService->findBySlug($entityFullName, $slug);
        $now = new DateTime();

        if (!$element) {
            $redirect301 = $this->em->getRepository(Redirect301::class)->findOneBy(['oldSlug' => $slug, 'objectType' => $entityFullName]);
            if ($redirect301) {
                $element = $this->em->getRepository($entityFullName)->find($redirect301->getObjectId());
                if ($element) {
                    $redirectUrl = $this->router->generate($route, ['entitySlug' => $entitySlug, 'slug' => $element->getSlug()]);
                    return new RedirectResponse($redirectUrl, 301);
                }
            }
            throw new NotFoundHttpException("Cette page n'existe pas! ( {$route} )");
        }

        if (property_exists($element, 'published') && $route !== 'single_preview') {
            if (!$element->getPublished()) {
                if ($this->checker->isGranted('ROLE_ADMIN')) {
                    return new RedirectResponse($this->router->generate('single_preview', ['entitySlug' => $entitySlug, 'slug' => $slug]));
                }
                throw new NotFoundHttpException("Cette page n'existe pas! ( {$entity} )");
            }

            if (property_exists($element, 'publishedAt') && ($element->getPublishedAt() > $now)) {
                if ($this->checker->isGranted('ROLE_ADMIN')) {
                    return new RedirectResponse($this->router->generate('single_preview', ['entitySlug' => $entitySlug, 'slug' => $slug]));
                }
                throw new NotFoundHttpException("Cette page n'existe pas! ( {$entity} )");
            }
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if ($this->cmsService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entityFullName)) {
            $components = $this->em->getRepository(Component::class)->findBy(['type' => $entityFullName, 'typeId' => $element->getId(), 'isTemp' => ($route === 'single_preview'), 'parentComponent' => null], ['position' => 'ASC']);
        }

        // GET TEMPLATE
        $view = $this->filesystem->exists($this->kernel->getProjectDir() . "/templates/{$entity}/single.html.twig") ? "{$entity}/single.html.twig" : '@AkyosCms/front/single.html.twig';
        $this->environment->addGlobal('global_element', $element);

        // RENDER
        return $this->environment->render($view, ['seo' => $this->em->getRepository(Seo::class)->findOneBy(['type' => $entityFullName, 'typeId' => $element->getId()]), 'element' => $element, 'components' => $components, 'entity' => $entity, 'slug' => $slug]);
    }

    /**
     * @param string $slug
     * @param string $route
     * @return string|RedirectResponse
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function pageAndPreview(string $slug, string $route)
    {
        // FIND PAGE
        $entity = Page::class;
        $slug = str_ends_with($slug, "/") ? substr($slug, 0, -1) : $slug;
        /** @var Page $page */
        $page = $this->cmsService->findBySlug($entity, $slug);
        $now = new DateTime();

        if (!$page) {
            $redirect301 = $this->em->getRepository(Redirect301::class)->findOneBy(['oldSlug' => $slug, 'objectType' => $entity]);
            if ($redirect301) {
                $page = $this->em->getRepository($entity)->find($redirect301->getObjectId());
                $redirectUrl = $this->router->generate($route, ['slug' => $page->getSlug()]);
                return new RedirectResponse($redirectUrl, 301);
            }
            throw new NotFoundHttpException("Cette page n'existe pas! ( {$entity} )");
        }

        if ($route !== 'page_preview') {
            if (!$page->getPublished()) {
                if ($this->checker->isGranted('ROLE_ADMIN')) {
                    return new RedirectResponse($this->router->generate('page_preview', ['slug' => $slug]));
                }
                throw new NotFoundHttpException("Cette page n'existe pas! ( {$entity} )");
            }

            if ($page->getPublishedAt() > $now) {
                if ($this->checker->isGranted('ROLE_ADMIN')) {
                    return new RedirectResponse($this->router->generate('page_preview', ['slug' => $slug]));
                }
                throw new NotFoundHttpException("Cette page n'existe pas! ( {$entity} )");
            }
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if ($this->cmsService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
            $components = $this->em->getRepository(Component::class)->findBy(['type' => $entity, 'typeId' => $page->getId(), 'isTemp' => ($route === 'page_preview'), 'parentComponent' => null], ['position' => 'ASC']);
        }

        // GET TEMPLATE
        $view = $page->getTemplate() ? 'page/' . $page->getTemplate() . '.html.twig' : '@AkyosCms/front/content.html.twig';

        $this->environment->addGlobal('global_page', $page);

        return $this->environment->render($view, ['seo' => $this->em->getRepository(Seo::class)->findOneBy(['type' => $entity, 'typeId' => $page->getId()]), 'page' => $page, 'components' => $components, 'content' => $page->getContent(), 'slug' => $slug]);
    }
}