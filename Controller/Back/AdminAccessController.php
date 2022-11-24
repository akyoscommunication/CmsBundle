<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\AdminAccess;
use Akyos\CmsBundle\Form\AdminAccessType;
use Akyos\CmsBundle\Repository\AdminAccessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

/**
 * Class AdminAccessController
 * @package Akyos\CmsBundle\Controller\Back
 */
#[Route(path: '/admin/gestion-des-droits', name: 'admin_access_')]
class AdminAccessController extends AbstractController
{
    /**
     * @param AdminAccessRepository $accessRepository
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    #[Route(path: '/', name: 'index')]
    public function index(AdminAccessRepository $accessRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $finder = new Finder();
        $finder->depth('== 0');
        // TODO => Améliorer la recherche des classes sans avoir à manipuler le nom du dossier vendor
        if (file_exists($this->getParameter('kernel.project_dir') . '/vendor/akyos')) {
            foreach ($finder->directories()->in($this->getParameter('kernel.project_dir') . '/vendor/akyos') as $bundleDirectory) {
                $filename = ucfirst(explode('-', $bundleDirectory->getFilename())[0]).'Bundle';
                if (class_exists('Akyos\\' . $filename . '\Service\ExtendAdminAccess') && method_exists('Akyos\\' . $filename . '\Service\ExtendAdminAccess', 'setDefaults')) {
                    $this->forward('Akyos\\' . $filename . '\Service\ExtendAdminAccess::setDefaults', []);
                }
            }
        }
        $query = $accessRepository->searchByName($request->query->get('search'));
        $els = $paginator->paginate($query, $request->query->getInt('page', 1), 16);
        return $this->render('@AkyosCms/crud/index.html.twig', ['els' => $els, 'title' => 'Accès admin', 'entity' => AdminAccess::class, 'route' => 'admin_access', 'search' => true, 'fields' => ['Id' => 'Id', 'Nom' => 'Name', 'Slug' => 'Slug', 'Roles' => 'Roles'],]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse|Response
     */
    #[Route(path: '/nouveau', name: 'new')]
    public function new(Request $request, EntityManagerInterface $entityManager)
    {
        $adminAccess = new AdminAccess();
        $form = $this->createForm(AdminAccessType::class, $adminAccess);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($adminAccess);
            $entityManager->flush();
            return $this->redirectToRoute('admin_access_index');
        }
        return $this->render('@AkyosCms/crud/new.html.twig', ['form' => $form->createView(), 'el' => $adminAccess, 'title' => 'Accès admin', 'entity' => 'AdminAccess', 'route' => 'admin_access',]);
    }

    /**
     * @param AdminAccess $adminAccess
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse|Response
     */
    #[Route(path: '/edit/{id}', name: 'edit')]
    public function edit(AdminAccess $adminAccess, Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(AdminAccessType::class, $adminAccess);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($adminAccess);
            $entityManager->flush();
            return $this->redirectToRoute('admin_access_edit', ['id' => $adminAccess->getId()]);
        }
        return $this->render('@AkyosCms/crud/edit.html.twig', ['el' => $adminAccess, 'title' => 'Accès admin ' . $adminAccess->getName(), 'entity' => 'AdminAccess', 'route' => 'admin_access', 'form' => $form->createView(),]);
    }

    /**
     * @param AdminAccess $adminAccess
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse
     * @throws Exception
     */
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(AdminAccess $adminAccess, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        if (!$adminAccess->getIsLocked()) {
            if ($this->isCsrfTokenValid('delete' . $adminAccess->getId(), $request->request->get('_token'))) {
                $entityManager->remove($adminAccess);
                $entityManager->flush();
                return $this->redirectToRoute('admin_access_index');
            }
            throw new InvalidCsrfTokenException('impossible de supprimer, csrf invalide');
        }
        throw new RuntimeException('Supprimer cet objet est interdit');
    }
}
