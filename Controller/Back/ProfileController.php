<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\User;
use Akyos\CmsBundle\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/profile', name: 'profile_')]
#[IsGranted('profil')]
class ProfileController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route(path: '/', name: 'index')]
    public function index(): Response
    {
        return $this->render('@AkyosCms/profile/index.html.twig', ['title' => 'Votre profil', 'user' => $this->getUser(),]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse|Response
     */
    #[Route(path: '/{id}/edit', name: 'edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('profile_index');
        }
        return $this->render('@AkyosCms/profile/edit.html.twig', ['title' => 'Votre profil', 'form' => $form->createView(),]);
    }
}
