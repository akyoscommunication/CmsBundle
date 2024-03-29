<?php

namespace Akyos\CmsBundle\Form;

use Akyos\CmsBundle\Entity\Option;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, ['attr' => ['placeholder' => "Nom du réglage",], 'label' => false])->add('slug', null, ['attr' => ['placeholder' => "Slug du réglage",], 'label' => false])->add('optionCategory', null, ['placeholder' => 'Choisissez une zone',])->add('type', ChoiceType::class, ['choices' => ['Texte' => 'text', 'Zone de texte' => 'textarea', 'Image' => 'image', "Galerie d'images" => 'gallery', 'Lien interne' => 'pagelink', 'Lien externe' => 'link', 'Téléphone' => 'tel', 'Email' => 'mail'], 'label' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Option::class,]);
    }
}
