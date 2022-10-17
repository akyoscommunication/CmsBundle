<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\User;
use Akyos\CmsBundle\Form\UserType;
use Akyos\CmsBundle\Form\UserEditType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
     * @param UserPasswordHasherInterface $passwordHasher
     * @return RedirectResponse|Response
     */
    #[Route(path: '/{id}/edit', name: 'edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $form = $this->createForm(UserEditType::class, $user, ['hasPasswordField' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('modifyPassword')->getData() and array_key_exists('password', $form->getExtraData())) {
                $password = $form->getExtraData()['password'];
                $user->setPassword($passwordHasher->hashPassword($user, $password));
            }
            $entityManager->flush();

            return $this->redirectToRoute('profile_index');
        }
        return $this->render('@AkyosCms/profile/edit.html.twig', ['title' => 'Votre profil', 'form' => $form->createView(),]);
    }

    #[Route(path: '/modify-password', name: 'modify_password')]
    public function getUserEditTypePasswordField(Request $request) {
        $modifyPassword = $request->query->get('modifyPassword') === "true";
        $form = $this->createForm(UserEditType::class, new User(), [
            'hasPasswordField' => true,
            'modifyPassword' => $modifyPassword,
        ]);

        return $this->render('@AkyosCms/profile/password_field.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
