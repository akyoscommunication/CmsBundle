<?php

declare(strict_types=1);

namespace Akyos\CmsBundle\Twig;

use Akyos\CmsBundle\Repository\CmsOptionsRepository;
use Akyos\CmsBundle\Repository\RgpdOptionsRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class GlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(protected CmsOptionsRepository $cmsOptionsRepository, protected RgpdOptionsRepository $rgpdOptionsRepository)
    {
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
