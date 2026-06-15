<?php

namespace Akyos\CmsBundle\Controller\Back;

use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Writer;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\ObjectType;

#[Route(path: '/admin/export', name: 'export_')]
#[IsGranted('exports')]
class ExportController extends AbstractController
{
    /**
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route(path: '/', name: 'index')]
    public function index(EntityManagerInterface $em): Response
    {
        $entities = [];
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entities[] = $m->getName();
        }
        return $this->render('@AkyosCms/export/index.html.twig', ['title' => 'Exporter', 'entities' => $entities]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(path: '/entity/params', name: 'entity_params')]
    public function getEntityParameter(Request $request): JsonResponse
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();
        $listExtractors = [$reflectionExtractor];
        $typeExtractors = [$phpDocExtractor, $reflectionExtractor];
        $propertyInfo = new PropertyInfoExtractor($listExtractors, $typeExtractors);
        $returnedTab = [];
        $allreadyCheck = [];
        $properties = $propertyInfo->getProperties($request->request->get('entity'));
        $returnedTab = $this->pushProperties($request->request->get('entity'), $properties, $propertyInfo, $returnedTab, $allreadyCheck, "");
        return new JsonResponse($returnedTab);
    }

    /**
     * @param $entity
     * @param $properties
     * @param $propertyInfo
     * @param $returnedTab
     * @param $allreadyCheck
     * @param $currentDepth
     * @return mixed
     */
    public function pushProperties($entity, $properties, $propertyInfo, $returnedTab, $allreadyCheck, $currentDepth)
    {
        if (!$properties) {
            return $returnedTab;
        }
        $allreadyCheck[] = $entity;
        foreach ($properties as $propertyName) {
            $type = $propertyInfo->getType($entity, $propertyName);
            if (!$type) {
                $returnedTab[] = ['name' => $currentDepth . $propertyName, 'class' => $entity];
                continue;
            }
            if ($this->isCollectionType($type)) {
                continue;
            }
            $className = $this->extractObjectClassName($type);
            if ($className && !in_array($className, $allreadyCheck, true) && str_contains($className, '\\')) {
                $returnedTab = $this->pushProperties(
                    $className,
                    $propertyInfo->getProperties($className) ?? [],
                    $propertyInfo,
                    $returnedTab,
                    $allreadyCheck,
                    $currentDepth . $propertyName . '.',
                );
            } else {
                $returnedTab[] = ['name' => $currentDepth . $propertyName, 'class' => $className ?? $entity];
            }
        }
        return $returnedTab;
    }

    private function isCollectionType(Type $type): bool
    {
        foreach ($type->traverse() as $traversedType) {
            if ($traversedType instanceof CollectionType) {
                return true;
            }
        }

        return false;
    }

    private function extractObjectClassName(Type $type): ?string
    {
        foreach ($type->traverse() as $traversedType) {
            if ($traversedType instanceof ObjectType) {
                return $traversedType->getClassName();
            }
        }

        return null;
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/dl', name: 'entity_dl')]
    public function download(Request $request, EntityManagerInterface $entityManager): Response
    {
        $els = $entityManager->getRepository($request->request->get('entity'))->findAll();
        $rows = $request->request->get('rows');
        $filename = 'export.csv';
        $csv = Writer::createFromString();
        $records = [$rows];
        foreach ($els as $el) {
            $record = [];
            foreach ($rows as $row) {
                if (count(explode('.', $row)) > 1) {
                    $value = $el;
                    foreach (explode('.', $row) as $method) {
                        $value = $value->{'get' . ucfirst($method)}();
                        if (!$value) {
                            break;
                        }
                    }
                    $record[] = $this->valueToString($value);
                } else {
                    $value = $el->{'get' . ucfirst($row)}();
                    $record[] = $this->valueToString($value);
                }
            }
            $records[] = $record;
        }
        $csv->insertAll($records);
        $response = new Response($csv->getContent());
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename=' . $filename);
        return $response;
    }

    /**
     * @param $value
     * @return string
     */
    public function valueToString($value): string
    {
        if (is_array($value)) {
            $value = implode('|', $value);
        }
        if (is_object($value)) {
            switch ($value::class) {
                case 'DateTime':
                    $value = $value->format('d/m/Y H:i:s');
                    break;
                case 'Date':
                    $value = $value->format('d/m/Y');
                    break;
                default:
                    $value = $value->toString();
            }
        }
        return $value;
    }
}
