<?php

namespace Akyos\CmsBundle\Form\Type\Page;

use Akyos\CmsBundle\Entity\Page;
use Akyos\FileManagerBundle\Form\Type\FileManagerType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Page $page */
        $page = $builder->getData();

        $builder->add('title', TextType::class, ['label' => 'Titre de la page',]);
        if ($page->getSlug()) {
            $builder->add('slug', TextType::class, ['label' => 'Slug de la page',]);
        }
        $builder->add('published', CheckboxType::class, ['label' => 'Publiée ?',])->add('template', TextType::class, ['label' => 'Template de la page', 'required' => false])->add('content', CKEditorType::class, ['required' => false, 'config' => ['placeholder' => "Texte", 'height' => 50, 'entities' => false, 'basicEntities' => false, 'entities_greek' => false, 'entities_latin' => false,], 'label' => 'Contenu de la page'])->add('thumbnail', FileManagerType::class, ['label' => 'Image de mise en avant',])->add('publishedAt', DateType::class, ['widget' => 'single_text', 'label' => 'Date de publication']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Page::class,]);
    }
}
