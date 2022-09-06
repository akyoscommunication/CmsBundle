<?php

namespace Akyos\CmsBundle\Twig;

use Akyos\CmsBundle\Controller\Back\CmsBundleController;
use Akyos\CmsBundle\Entity\Option;
use Akyos\CmsBundle\Entity\OptionCategory;
use Akyos\CmsBundle\Repository\CmsOptionsRepository;
use Akyos\CmsBundle\Service\CmsService;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Akyos\BuilderBundle\Entity\BuilderOptions;
use Akyos\BuilderBundle\AkyosBuilderBundle;
use Akyos\BuilderBundle\Service\Builder;

class CmsExtension extends AbstractExtension
{
    private CmsBundleController $cmsBundleController;

    private EntityManagerInterface $em;

    private UrlGeneratorInterface $router;

    private CmsOptionsRepository $cmsOptionsRepository;

    private CmsService $cmsService;

    private ContainerInterface $container;

    public function __construct(CmsBundleController $cmsBundleController, EntityManagerInterface $entityManager, UrlGeneratorInterface $router, CmsOptionsRepository $cmsOptionsRepository, CmsService $cmsService, ContainerInterface $container)
    {
        $this->cmsBundleController = $cmsBundleController;
        $this->em = $entityManager;
        $this->router = $router;
        $this->cmsOptionsRepository = $cmsOptionsRepository;
        $this->cmsService = $cmsService;
        $this->container = $container;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [new TwigFunction('dynamicVariable', [$this, 'dynamicVariable']), new TwigFunction('hasSeo', [$this, 'hasSeo']), new TwigFunction('getEntitySlug', [$this, 'getEntitySlug']), new TwigFunction('getEntityNameSpace', [$this, 'getEntityNameSpace']), new TwigFunction('isArchive', [$this, 'isArchive']), new TwigFunction('getMenu', [$this, 'getMenu']), new TwigFunction('useClosure', [$this, 'useClosure']), new TwigFunction('getOption', [$this, 'getOption']), new TwigFunction('getOptions', [$this, 'getOptions']), new TwigFunction('getElementSlug', [$this, 'getElementSlug']), new TwigFunction('getElement', [$this, 'getElement']), new TwigFunction('getElementsList', [$this, 'getElementsList']), new TwigFunction('getCategoryList', [$this, 'getCategoryList']), new TwigFunction('getPermalink', [$this, 'getPermalink']), new TwigFunction('getPermalinkById', [$this, 'getPermalinkById']), new TwigFunction('checkChildActive', [$this, 'checkChildActive']), new TwigFunction('getCustomField', [$this->cmsService, 'getCustomField']), new TwigFunction('setCustomField', [$this->cmsService, 'setCustomField']), new TwigFunction('searchByCustomField', [$this->cmsService, 'searchByCustomField']), new TwigFunction('getBundleTab', [$this, 'getBundleTab']), new TwigFunction('getBundleTabContent', [$this, 'getBundleTabContent']),];
    }

    /**
     * @param $el
     * @param $field
     * @return mixed
     */
    public function dynamicVariable($el, $field)
    {
        $getter = 'get' . $field;
        if (count(explode(';', $field)) > 1) {
            $getter1 = 'get' . explode(';', $field)[0];
            $getter2 = 'get' . explode(';', $field)[1];
            $value = $el->$getter1() ? $el->$getter1()->$getter2() : '';
        } else {
            $value = $el->$getter();
        }
        if (is_array($value)) {
            $arrayValue = "";
            foreach ($value as $key => $item) {
                $arrayValue .= $item;
                if ($key !== (count($value) - 1)) {
                    $arrayValue .= ", ";
                }
            }
            return $arrayValue;
        }
        return $value;
    }

    /**
     * @param $entity
     * @return bool
     */
    public function hasSeo($entity): bool
    {
        return $this->cmsService->checkIfSeoEnable($entity) ?: false;
    }

    /**
     * @param $entity
     * @param $page
     * @return bool
     */
    public function isArchive($entity, $page): bool
    {
        if (!is_array($page)) {
            return false;
        }

        if (!empty($page) && !is_object($page[0])) {
            return false;
        }
        return (!empty($page) ? ($entity === get_class($page[0])) : false);
    }

    /**
     * @param $menuSlug
     * @param $page
     * @return string
     */
    public function getMenu($menuSlug, $page): string
    {
        return $this->cmsBundleController->renderMenu($menuSlug, $page);
    }

    /**
     * @param \Closure $closure
     * @param $params
     * @return mixed
     */
    public function useClosure(\Closure $closure, $params)
    {
        return $closure($params);
    }

    /**
     * @param $optionSlug
     * @return object|null
     */
    public function getOption($optionSlug): ?object
    {
        return $this->em->getRepository(Option::class)->findOneBy(['slug' => $optionSlug]);
    }

    /**
     * @param $optionsSlug
     * @return array
     * @throws JsonException
     */
    public function getOptions($optionsSlug): array
    {
        $result = null;
        /** @var OptionCategory $options */
        $options = $this->em->getRepository(OptionCategory::class)->findOneBy(['slug' => $optionsSlug]);
        /** @var Option $option */
        foreach ($options->getOptions() as $option) {
            if ($option instanceof Option) {
                $result[$option->getSlug()] = $option->getValue();
            }
        }

        return $result;
    }

    /**
     * @param $type
     * @return false|object[]|null
     */
    public function getElementsList($type)
    {
        if (!$type) {
            return false;
        }

        if (false !== stripos($type, "Category")) {
            str_replace('Category', '', $type);
        }

        $entityFullName = null;
        $entityFields = null;
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entityName = explode('\\', $m->getName());
            $entityName = $entityName[count($entityName) - 1];
            if (preg_match('/^' . $type . '$/i', $entityName)) {
                $entityFullName = $m->getName();
                $entityFields = $m->getFieldNames();
            }
        }

        if ($entityFullName) {
            if (in_array('position', $entityFields, true)) {
                $elements = $this->em->getRepository($entityFullName)->findBy([], ['position' => 'ASC']);
            } else {
                $elements = $this->em->getRepository($entityFullName)->findAll();
            }
        }

        return ($elements ?? null);
    }

    /**
     * @param $type
     * @return false|object[]|null
     */
    public function getCategoryList($type)
    {
        if (!$type) {
            return false;
        }

        $type .= 'Category';

        $entityFullName = null;
        $entityFields = null;
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entityName = explode('\\', $m->getName());
            $entityName = $entityName[count($entityName) - 1];
            if (preg_match('/^' . $type . '$/i', $entityName)) {
                $entityFullName = $m->getName();
                $entityFields = $m->getFieldNames();
            }
        }

        if ($entityFullName) {
            if (in_array('position', $entityFields, true)) {
                $elements = $this->em->getRepository($entityFullName)->findBy([], ['position' => 'ASC']);
            } else {
                $elements = $this->em->getRepository($entityFullName)->findAll();
            }
        }

        return ($elements ?? null);
    }

    /**
     * @param $type
     * @param $id
     * @return string|null
     */
    public function getPermalinkById($type, $id): ?string
    {
        $link = '';
        if ($type === 'Page' && $id) {
            $cmsOptions = $this->cmsOptionsRepository->findAll();
            $homepage = $cmsOptions[0]->getHomepage();
            $isHome = false;
            if ($homepage && $homepage->getId() === $id) {
                $isHome = true;
            }
            if ($isHome) {
                $link = $this->router->generate('home');
            } else {
                $link = $this->router->generate('page', ['slug' => $this->getElementSlug($type, $id)]);
            }
        } elseif (($type !== 'Page') && $id) {
            $link = $this->router->generate('single', ['entitySlug' => $this->getEntitySlug($type), 'slug' => $this->getElementSlug($type, $id)]);
        } elseif (($type !== 'Page') && !$id) {
            $link = $this->router->generate('archive', ['entitySlug' => $this->getEntitySlug($type)]);
        } else {
            $link = null;
        }

        return $link;
    }

    /**
     * @param $type
     * @param $typeId
     * @return false|string
     */
    public function getElementSlug($type, $typeId)
    {
        if (false !== stripos($type, "Category")) {
            $entity = str_replace('Category', '', $type);
        }

        $entityFullName = null;
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entityName = explode('\\', $m->getName());
            $entityName = $entityName[count($entityName) - 1];
            if (preg_match('/^' . $type . '$/i', $entityName)) {
                $entityFullName = $m->getName();
            }
        }
        $el = $this->em->getRepository($entityFullName)->find($typeId);

        if (!$el) {
            return false;
        }

        return $el->getSlug();
    }

    /**
     * @param $entity
     * @return false
     */
    public function getEntitySlug($entity): bool|string
    {
        if (!class_exists($entity)) {
            $entity = $this->getEntityNameSpace($entity);
        }
        return defined($entity . '::ENTITY_SLUG') ? $entity::ENTITY_SLUG : false;
    }

    /**
     * @param $entity
     * @return ?string
     */
    public function getEntityNameSpace($entity): ?string
    {
        $entityFullName = null;
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entityName = explode('\\', $m->getName());
            $entityName = $entityName[count($entityName) - 1];
            if (preg_match('/^' . $entity . '$/i', $entityName)) {
                $entityFullName = $m->getName();
            }
        }
        if (!$entityFullName) {
            return $entity;
        }
        return $entityFullName;
    }

    /**
     * @param $item
     * @return string|null
     */
    public function getPermalink($item): ?string
    {
        $urlPaterne = "/^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:_\/?#[\]@!\$&'\(\)\*\+,;=.]+$/";
        $link = '';
        if ($item->getUrl()) {
            if (preg_match($urlPaterne, $item->getUrl())) {
                $link = $item->getUrl();
            } else {
                $link = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . $item->getUrl());
            }
        } elseif ($item->getType()) {
            if (($item->getType() === 'Page') && $item->getIdType()) {
                $cmsOptions = $this->cmsOptionsRepository->findAll();
                $homepage = $cmsOptions[0]->getHomepage();
                $isHome = false;
                if ($homepage && $homepage->getId() === $item->getIdType()) {
                    $isHome = true;
                }
                if ($isHome) {
                    $link = $this->router->generate('home');
                } else {
                    $link = $this->router->generate('page', ['slug' => $this->getElementSlug($item->getType(), $item->getIdType())]);
                }
            } elseif (($item->getType() !== 'Page') && $item->getIdType()) {
                $slug = $this->getElementSlug($item->getType(), $item->getIdType());
                if ($slug) {
                    $link = $this->router->generate('single', ['entitySlug' => $this->getEntitySlug($item->getType()), 'slug' => $slug]);
                }
            } elseif (($item->getType() !== 'Page') && !$item->getIdType()) {
                $link = $this->router->generate('archive', ['entitySlug' => $this->getEntitySlug($item->getType())]);
            } else {
                $link = null;
            }
        }

        return $link;
    }

    /**
     * @param $item
     * @param $current
     * @return bool
     */
    public function checkChildActive($item, $current): bool
    {
        foreach ($item->getMenuItemsChilds() as $child) {
            if ($child && $current === $this->getElement($child->getType(), $child->getIdType())) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $type
     * @param $typeId
     * @return false|object|string
     */
    public function getElement($type, $typeId)
    {
        if (!$typeId) {
            return false;
        }

        if (false !== stripos($type, "Category")) {
            str_replace('Category', '', $type);
        }

        $entityFullName = null;
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entityName = explode('\\', $m->getName());
            $entityName = $entityName[count($entityName) - 1];
            if (preg_match('/^' . $type . '$/i', $entityName)) {
                $entityFullName = $m->getName();
            }
        }

        if ($entityFullName) {
            $slug = $this->em->getRepository($entityFullName)->find($typeId);
        } else {
            $slug = "page_externe";
        }

        return $slug;
    }

    /**
     * @param $objectType
     * @return string
     */
    public function getBundleTab($objectType): string
    {
        $html = '';
        $class = Builder::class;
        if (class_exists($class) && $this->cmsService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $objectType)) {
            $html .= $this->container->get('render.builder')->getTab();
        }

        return $html;
    }

    /**
     * @param $objectType
     * @param $objectId
     * @return string
     */
    public function getBundleTabContent($objectType, $objectId): string
    {
        $html = '';
        $class = Builder::class;
        if (class_exists($class) && $this->cmsService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $objectType)) {
            $html .= $this->container->get('render.builder')->getTabContent($objectType, $objectId);
        }

        return $html;
    }
}
