<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\Seo;
use Akyos\CmsBundle\Form\SeoType;
use Akyos\CmsBundle\Repository\SeoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/seo', name: 'seo_')]
class SeoController extends AbstractController
{
    /**
     * @param $type
     * @param $typeId
     * @param SeoRepository $seoRepository
     * @return Response
     */
    #[Route(path: '/render', name: 'render', methods: ['GET'])]
    public function index($type, $typeId, SeoRepository $seoRepository): Response
    {
        $type = urldecode($type);
        $seo = $seoRepository->findOneBy(['type' => $type, 'typeId' => $typeId]);
        if (!$seo) {
            $seo = new Seo();
            $seo->setTypeId($typeId);
            $seo->setType($type);
        }
        $formSeo = $this->createForm(SeoType::class, $seo);
        return $this->render('@AkyosCms/seo/render.html.twig', ['formSeo' => $formSeo->createView(),]);
    }

    /**
     * @param $type
     * @param $typeId
     * @param Request $request
     * @param SeoRepository $seoRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route(path: '/submit/{type}/{typeId}', name: 'submit', methods: ['POST'], options: ['expose' => true])]
    public function submit($type, $typeId, Request $request, SeoRepository $seoRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $type = urldecode($type);
        $seo = $seoRepository->findOneBy(['type' => $type, 'typeId' => $typeId]);
        if (!$seo) {
            $seo = new Seo();
            $seo->setTypeId($typeId);
            $seo->setType($type);
        }
        $formSeo = $this->createForm(SeoType::class, $seo);
        $formSeo->handleRequest($request);
        if ($formSeo->isSubmitted() && $formSeo->isValid()) {
            $entityManager->persist($seo);
            $entityManager->flush();

            return new JsonResponse('valid');
        }
        return new JsonResponse('non');
    }
}
