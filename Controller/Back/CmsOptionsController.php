<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\CmsOptions;
use Akyos\CmsBundle\Form\CmsOptionsType;
use Akyos\CmsBundle\Repository\CmsOptionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/cms/options', name: 'cms_options')]
class CmsOptionsController extends AbstractController
{
    /**
     * @param CmsOptionsRepository $cmsOptionsRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/', name: '', methods: ['GET', 'POST'])]
    public function index(CmsOptionsRepository $cmsOptionsRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $cmsOption = $cmsOptionsRepository->findAll();
        if (!$cmsOption) {
            $cmsOption = new CmsOptions();
        } else {
            $cmsOption = $cmsOption[0];
        }
        $entities = [];
        $meta = $entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entities[] = $m->getName();
        }
        $form = $this->createForm(CmsOptionsType::class, $cmsOption, ['entities' => $entities]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cmsOption);
            $entityManager->flush();

            return $this->redirectToRoute('cms_options');
        }
        return $this->render('@AkyosCms/cms_options/new.html.twig', ['cms_option' => $cmsOption, 'form' => $form->createView(),]);
    }
}
