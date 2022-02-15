<?php

namespace Akyos\CmsBundle\Form;

use Akyos\CmsBundle\Entity\Menu;
use Akyos\CmsBundle\Entity\MenuArea;
use Akyos\CmsBundle\Repository\MenuAreaRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('title', null, [
				'label' => 'Titre du menu',
				'help' => 'Insérez votre titre ici'
			])
			->add('menuArea', EntityType::class, [
				'label' => 'Zone de menu',
				'help' => 'Ce menu doit-il apparaître dans une zone de menu ?',
				'required' => false,
				'class' => MenuArea::class,
				'query_builder' => function (MenuAreaRepository $er) {
					return $er->createQueryBuilder('ma')
						->orderBy('ma.name', 'ASC');
				},
				'choice_label' => 'name',
				'placeholder' => "Choisissez une zone"
			]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Menu::class,
		]);
	}
}
