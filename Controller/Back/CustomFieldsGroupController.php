<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\CustomField;
use Akyos\CmsBundle\Entity\CustomFieldsGroup;
use Akyos\CmsBundle\Entity\CustomFieldValue;
use Akyos\CmsBundle\Form\Type\CustomFields\CustomFieldsGroupType;
use Akyos\CmsBundle\Form\Type\CustomFields\NewCustomFieldsGroupType;
use Akyos\CmsBundle\Repository\CustomFieldRepository;
use Akyos\CmsBundle\Repository\CustomFieldsGroupRepository;
use Akyos\CmsBundle\Repository\CustomFieldValueRepository;
use Akyos\CoreBundle\Form\Handler\CrudHandler;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Translation;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/custom-fields-group', name: 'custom_fields_group_')]
#[IsGranted('champs-personnalises')]
class CustomFieldsGroupController extends AbstractController
{
    /**
     * @param CustomFieldsGroupRepository $customFieldsGroupRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param CrudHandler $crudHandler
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/', name: 'index', methods: ['GET', 'POST'])]
    public function index(CustomFieldsGroupRepository $customFieldsGroupRepository, PaginatorInterface $paginator, Request $request, CrudHandler $crudHandler, EntityManagerInterface $entityManager): Response
    {
        $entities = [];
        $meta = $entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if (!preg_match('/Component|Option|ContactForm/i', $m->getName()) && stripos($m->getName(), 'Akyos') !== false) {
                $entities[] = $m->getName();
            }
        }
        $query = $customFieldsGroupRepository->createQueryBuilder('a');
        if ($request->query->get('search')) {
            $query->andWhere('a.title LIKE :keyword OR a.slug LIKE :keyword OR a.description LIKE :keyword')->setParameter('keyword', '%' . $request->query->get('search') . '%');
        }
        $els = $paginator->paginate($query->getQuery(), $request->query->getInt('page', 1), 12);
        $customFieldsGroup = new CustomFieldsGroup();
        $customFieldsGroupForm = $this->createForm(NewCustomFieldsGroupType::class, $customFieldsGroup, ['entities' => $entities]);
        if ($crudHandler->new($customFieldsGroupForm, $request)) {
            return $this->redirectToRoute('custom_fields_group_edit', ['id' => $customFieldsGroup->getId()]);
        }
        return $this->render('@AkyosCms/crud/index.html.twig', ['els' => $els, 'title' => 'Groupes de champs', 'entity' => 'CustomFieldsGroup', 'route' => 'custom_fields_group', 'formModal' => $customFieldsGroupForm->createView(), 'fields' => ['ID' => 'Id', 'Nom' => 'Title', 'Entité' => 'Entity',],]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entities = [];
        $meta = $entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if (!preg_match('/Component|Option|ContactForm/i', $m->getName()) && stripos($m->getName(), 'Akyos') !== false) {
                $entities[] = $m->getName();
            }
        }
        $customFieldsGroup = new CustomFieldsGroup();
        $form = $this->createForm(NewCustomFieldsGroupType::class, $customFieldsGroup, ['entities' => $entities]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($customFieldsGroup);
            $entityManager->flush();

            return $this->redirectToRoute('custom_fields_group_index');
        }
        return $this->render('@AkyosCms/crud/new.html.twig', ['el' => $customFieldsGroup, 'title' => 'Groupe de champs', 'entity' => 'CustomFieldsGroup', 'route' => 'custom_fields_group', 'form' => $form->createView(),]);
    }

    /**
     * @param Request $request
     * @param CustomFieldsGroup $customFieldsGroup
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CustomFieldsGroup $customFieldsGroup, EntityManagerInterface $entityManager): Response
    {
        $akyosEntities = [];
        $entities = [];
        $meta = $entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if (!preg_match('/Component|Option|ContactForm/i', $m->getName()) && stripos($m->getName(), 'Akyos') !== false) {
                $akyosEntities[] = $m->getName();
            }
            if (!preg_match('/Component|Option|ContactForm/i', $m->getName())) {
                $entities[] = $m->getName();
            }
        }
        $form = $this->createForm(CustomFieldsGroupType::class, $customFieldsGroup, ['akyosEntities' => $akyosEntities, 'entities' => $entities,]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('custom_fields_group_index');
        }
        return $this->render('@AkyosCms/crud/edit.html.twig', ['el' => $customFieldsGroup, 'title' => 'Groupe de champs', 'entity' => CustomFieldsGroup::class, 'route' => 'custom_fields_group', 'form' => $form->createView(),]);
    }

    /**
     * @param Request $request
     * @param CustomFieldsGroup $customFieldsGroup
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, CustomFieldsGroup $customFieldsGroup, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $customFieldsGroup->getId(), $request->request->get('_token'))) {
            $entityManager->remove($customFieldsGroup);
            $entityManager->flush();
        }
        return $this->redirectToRoute('custom_fields_group_index');
    }

    /**
     * @param $id
     * @param $slug
     * @param $callback
     * @param Request $request
     * @param CustomFieldValueRepository $customFieldValueRepository
     * @param CustomFieldRepository $customFieldRepository
     * @param EntityManagerInterface $entityManager
     * @return null
     */
    #[Route(path: '/change-value/{entity}/{id}/{slug}/{callback}', name: 'change_value', methods: ['POST'])]
    public function changeValue($id, $slug, $callback, Request $request, CustomFieldValueRepository $customFieldValueRepository, CustomFieldRepository $customFieldRepository, EntityManagerInterface $entityManager)
    {
        $newValue = $request->get('data');
        $customField = $customFieldRepository->findOneBy(['slug' => $slug]) ?? (!$entityManager->getMetadataFactory()->isTransient(Translation::class) ? $entityManager->getRepository(Translation::class)->findObjectByTranslatedField('slug', $slug, CustomField::class) : null);
        $customFieldValue = $customFieldValueRepository->findOneBy(['customField' => $customField, 'objectId' => $id]);
        if (!$customFieldValue) {
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->setCustomField($customField)->setValue($newValue)->setObjectId($id);
            $entityManager->persist($customFieldValue);
        } else {
            $customFieldValue->setValue($newValue);
        }
        $entityManager->flush();
        return $this->redirect(urldecode($callback));
    }
}
