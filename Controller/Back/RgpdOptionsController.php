<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\RgpdOptions;
use Akyos\CmsBundle\Form\RgpdOptionsType;
use Akyos\CmsBundle\Repository\RgpdOptionsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/admin/rgpd/options", name="rgpd_options")
 * @IsGranted("rgpd")
 */
class RgpdOptionsController extends AbstractController
{
	/**
	 * @Route("/", name="", methods={"GET", "POST"})
	 * @param RgpdOptionsRepository $rgpdOptionsRepository
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function index(RgpdOptionsRepository $rgpdOptionsRepository, Request $request): Response
	{
		$rgpdOption = $rgpdOptionsRepository->findAll();
		if (!$rgpdOption) {
			$rgpdOption = new RgpdOptions();
		} else {
			$rgpdOption = $rgpdOption[0];
		}

		$form = $this->createForm(RgpdOptionsType::class, $rgpdOption);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($rgpdOption);
			$entityManager->flush();

			return $this->redirectToRoute('rgpd_options');
		}

		return $this->render('@AkyosCms/rgpd_options/new.html.twig', [
			'rgpd_option' => $rgpdOption,
			'form' => $form->createView(),
		]);
	}
}
