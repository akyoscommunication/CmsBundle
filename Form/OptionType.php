<?php

namespace Akyos\CmsBundle\Form;

use Akyos\CmsBundle\Entity\Option;
use Akyos\FileManagerBundle\Form\Type\FileManagerCollectionType;
use Akyos\FileManagerBundle\Form\Type\FileManagerType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OptionType extends AbstractType
{
    protected ?int $optionId = null;

    protected array $pages;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->optionId = $options['option'];
        $this->pages = $options['pages'];

        switch ($builder->getData()->getType()) {
            case 'textarea':
                $builder->add('value', CKEditorType::class, ['required' => false, 'config' => ['placeholder' => "Texte", 'height' => 50, 'entities' => false, 'basicEntities' => false, 'entities_greek' => false, 'entities_latin' => false,], 'label' => false,]);
                break;

            case 'tel':
                $builder->add('value', TelType::class, ['attr' => ['placeholder' => "Numéro",], 'label' => false, 'required' => false,]);
                break;

            case 'mail':
                $builder->add('value', EmailType::class, ['attr' => ['placeholder' => "Email",], 'label' => false, 'required' => false]);
                break;

            case 'pagelink':
                $builder->add('value', ChoiceType::class, ['choices' => $this->pages, 'label' => false]);
                break;

            case 'link':
                $builder->add('value', UrlType::class, ['attr' => ['placeholder' => "Lien",], 'label' => false, 'required' => false]);
                break;

            case 'image':
                $builder->add('value', FileManagerType::class, ['config' => 'full']);
                break;

            case 'gallery':
                $builder->add('value', FileManagerCollectionType::class);
                break;

            default:
                $builder->add('value', TextType::class, ['attr' => ['placeholder' => "Valeur",], 'label' => false, 'required' => false]);
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Option::class, 'option' => null, 'pages' => null]);
    }

    public function getBlockPrefix(): string
    {
        return 'ac_back_option_form' . $this->optionId;
    }
}
