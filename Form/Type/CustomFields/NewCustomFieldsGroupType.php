<?php

namespace Akyos\CmsBundle\Form\Type\CustomFields;

use Akyos\CmsBundle\Entity\CustomFieldsGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewCustomFieldsGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, ['label' => 'Nom du groupe', 'help' => 'Donnez un titre à votre groupe de champs !',])->add('entity', ChoiceType::class, ['label' => 'Entité liée', 'help' => 'A quelle entité souhaitez vous ajouter des champs ?', 'required' => false, 'choices' => $options['entities'], 'choice_label' => fn($choice, $key, $value) => $value, 'multiple' => false, 'expanded' => false,]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CustomFieldsGroup::class, 'entities' => null,]);
    }
}
