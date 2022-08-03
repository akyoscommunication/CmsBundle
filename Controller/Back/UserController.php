<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\User;
use Akyos\CmsBundle\Form\UserEditType;
use Akyos\CmsBundle\Form\UserType;
use Akyos\CmsBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/user', name: 'user_')]
class UserController extends AbstractController
{
    /**
     * @param UserRepository $userRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param ParameterBagInterface $parameterBag
     * @return Response
     */
    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request, ParameterBagInterface $parameterBag): Response
    {
        $roles = $parameterBag->get('user_roles');
        $flippedRoles = array_flip($roles);
        $query = $userRepository->createQueryBuilder('a');
        $keyword = $request->query->get('search');
        if ($keyword) {
            if (array_key_exists($keyword, $roles)) {
                $keyword = $roles[$keyword];
            }
            $query->andWhere('a.email LIKE :keyword OR a.roles LIKE :keyword')->setParameter('keyword', '%' . $keyword . '%');
        }
        $els = $paginator->paginate($query->getQuery(), $request->query->getInt('page', 1), 12);
        foreach ($els as $user) {
            $newUserRoles = array_map(static function ($n) use ($flippedRoles) {
                return $flippedRoles[$n];
            }, $user->getRoles());
            $user->setRoles($newUserRoles);
        }
        return $this->render('@AkyosCms/crud/index.html.twig', ['els' => $els, 'title' => 'Utilisateurs', 'entity' => 'User', 'route' => 'user', 'fields' => ['ID' => 'Id', 'Email' => 'Email', 'Rôles' => 'RolesDisplay'],]);
    }

    /**
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordHasher
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }
        return $this->render('@AkyosCms/crud/new.html.twig', ['el' => $user, 'title' => 'Utilisateur', 'entity' => 'User', 'route' => 'user', 'form' => $form->createView(),]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }
        return $this->render('@AkyosCms/crud/edit.html.twig', ['el' => $user, 'title' => 'Utilisateur', 'entity' => 'User', 'route' => 'user', 'form' => $form->createView(),]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }
        return $this->redirectToRoute('user_index');
    }
}
