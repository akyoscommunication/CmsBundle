<?php

namespace Akyos\CmsBundle\Controller\Back;

use Akyos\CmsBundle\Entity\Option;
use Akyos\CmsBundle\Form\NewOptionType;
use Akyos\CmsBundle\Form\OptionType;
use Akyos\CmsBundle\Repository\OptionCategoryRepository;
use Akyos\CmsBundle\Repository\OptionRepository;
use Akyos\CmsBundle\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/admin/site_option", name="option_")
 * @IsGranted("options-du-site")
 */
class OptionController extends AbstractController
{
	/**
	 * @Route("/", name="index", methods={"GET", "POST"})
	 * @param Request $request
	 * @param OptionRepository $optionRepository
	 * @param PageRepository $pageRepository
	 * @param OptionCategoryRepository $categoryRepository
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function index(Request $request, OptionRepository $optionRepository, PageRepository $pageRepository, OptionCategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
	{
		$option = new Option();
		$newOptionForm = $this->createForm(NewOptionType::class, $option);

		if ($request->getMethod() === 'POST') {
			$newOptionForm->handleRequest($request);
			if ($newOptionForm->isSubmitted() && $newOptionForm->isValid()) {
				try {
					$entityManager->persist($option);
					$entityManager->flush();
					$this->addFlash('success', "Création du réglage effectuée avec succès !");
				} catch (Exception $e) {
					$this->addFlash('danger', "Une erreur s'est produite lors de la création du réglage, merci de réssayer.");
				}
			}
		}

		$params =[];

		$pageArray =[];
		foreach ($pageRepository->findAll() as $page) {
			$pageArray[$page->getTitle()] = $request->getUriForPath('/' . $page->getSlug());
		}

		foreach ($optionRepository->findAll() as $option) {
			$optionForm = $this->createForm(OptionType::class, $option, ['option' => $option->getId(), 'pages' => $pageArray]);
			if ($request->getMethod() === 'POST') {
				$optionForm->handleRequest($request);
				if ($optionForm->isSubmitted() && $optionForm->isValid()) {
					try {
						$entityManager->persist($option);
						$entityManager->flush();
						$this->addFlash('success', "Modification du réglage effectuée avec succès !");
					} catch (Exception $e) {
						$this->addFlash('danger', "Une erreur s'est produite lors de la modification du réglage, merci de réssayer.");
					}
				}
			}
			$params[$option->getSlug()] = $optionForm->createView();
		}

		return $this->render('@AkyosCms/option/index.html.twig', [
			'options' => $categoryRepository->findAll(),
			'params' => $params,
			'new_option_form' => $newOptionForm->createView(),
			'title' => 'Réglages',
			'entity' => 'Option',
			'route' => 'option',
			'fields' => array(
				'Title' => 'Title',
				'ID' => 'Id',
				'Valeur' => 'Value'
			),
		]);
	}
	
	/**
	 * @Route("/remove/{id}", name="delete")
	 * @param Option $option
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function delete(Option $option, EntityManagerInterface $entityManager): Response
	{
		$entityManager->remove($option);
		$entityManager->flush();

		return $this->redirectToRoute('option_index');
	}
}
