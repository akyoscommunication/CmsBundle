<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\User;
use Akyos\CmsBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/admin/profile", name="profile_")
 * @IsGranted("profil")
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @return Response
     */
	public function index(): Response
    {
		return $this->render('@AkyosCms/profile/index.html.twig', [
			'title' => 'Votre profil',
			'user' => $this->getUser(),
		]);
	}

	/**
	 * @Route("/{id}/edit", name="edit")
	 * @param Request $request
	 * @param User $user
	 * @return RedirectResponse|Response
	 */
	public function edit(Request $request, User $user)
	{
		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->getDoctrine()->getManager()->flush();

			return $this->redirectToRoute('profile_index');
		}

		return $this->render('@AkyosCms/profile/edit.html.twig', [
			'title' => 'Votre profil',
			'form' => $form->createView(),
		]);
	}
}
