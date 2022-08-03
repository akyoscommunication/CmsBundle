<?php

namespace Akyos\CmsBundle\Twig;

use Akyos\CmsBundle\Repository\CmsOptionsRepository;
use Akyos\CmsBundle\Repository\RgpdOptionsRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class GlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    protected CmsOptionsRepository $cmsOptionsRepository;

    protected RgpdOptionsRepository $rgpdOptionsRepository;

    public function __construct(CmsOptionsRepository $cmsOptionsRepository, RgpdOptionsRepository $rgpdOptionsRepository)
    {
        $this->cmsOptionsRepository = $cmsOptionsRepository;
        $this->rgpdOptionsRepository = $rgpdOptionsRepository;
    }

    /**
     * @return array
     */
    public function getGlobals(): array
    {
        $cmsOptions = $this->cmsOptionsRepository->findAll();
        $rgpdOptions = $this->rgpdOptionsRepository->findAll();
        if ($cmsOptions) {
            $cmsOptions = $cmsOptions[0];
        }
        if ($rgpdOptions) {
            $rgpdOptions = $rgpdOptions[0];
        }
        return ['cms_options' => $cmsOptions, 'rgpd_options' => $rgpdOptions];
    }
}
