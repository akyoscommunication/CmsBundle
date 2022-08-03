<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Repository\CmsOptionsRepository;
use JsonException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/recaptcha', name: 'recaptcha_')]
class RecaptchaController extends AbstractController
{
    /**
     * @param string $token
     * @param CmsOptionsRepository $cmsOptionsRepository
     * @return JsonResponse
     * @throws JsonException
     */
    #[Route(path: '/recaptcha-v3-verify/{action}/{token}', name: 'v3_verify')]
    public function recaptchaV3Verify(string $token, CmsOptionsRepository $cmsOptionsRepository): JsonResponse
    {
        $cmsOptions = $cmsOptionsRepository->findAll();
        if ($cmsOptions) {
            $cmsOptions = $cmsOptions[0];
        }
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_private = ($cmsOptions->getRecaptchaPrivateKey() ?: null);
        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_private . '&response=' . $token);
        $recaptcha = json_decode($recaptcha, true, 512, JSON_THROW_ON_ERROR);
        if ($recaptcha->success && $recaptcha->score >= 0.8) {
            return new JsonResponse(['error' => false]);
        }
        return new JsonResponse(['error' => true, 'message' => 'La vérification recaptcha est invalide, ou le délai est expiré, veuillez réessayer l\'envoi du formulaire.']);
    }
}
