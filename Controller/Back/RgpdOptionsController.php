<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\RgpdOptions;
use Akyos\CmsBundle\Form\RgpdOptionsType;
use Akyos\CmsBundle\Repository\RgpdOptionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/rgpd/options', name: 'rgpd_options')]
#[IsGranted('rgpd')]
class RgpdOptionsController extends AbstractController
{
    /**
     * @param RgpdOptionsRepository $rgpdOptionsRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/', name: '', methods: ['GET', 'POST'])]
    public function index(RgpdOptionsRepository $rgpdOptionsRepository, Request $request, EntityManagerInterface $entityManager): Response
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
            $entityManager->persist($rgpdOption);
            $entityManager->flush();

            return $this->redirectToRoute('rgpd_options');
        }
        return $this->render('@AkyosCms/rgpd_options/new.html.twig', ['rgpd_option' => $rgpdOption, 'form' => $form->createView(),]);
    }
}
