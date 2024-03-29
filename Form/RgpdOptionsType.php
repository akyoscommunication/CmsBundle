<?php

namespace Akyos\CmsBundle\Form;

use Akyos\CmsBundle\Entity\Page;
use Akyos\CmsBundle\Entity\RgpdOptions;
use Akyos\CmsBundle\Repository\PageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RgpdOptionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('serviceUsed', ChoiceType::class, [
                'label' => "Service utilisé",
                'choices' => RgpdOptions::SERVICES,
                'required' => true,
            ])
            ->add('siteName', TextType::class, [
                'label' => 'Appellation du site sur la page Politique de confidentialité'
            ])
            ->add('contactMail', EmailType::class, [
                'label' => 'Email de contact du DPO'
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse de l\'entreprise'
            ])
            ->add('contactPage', EntityType::class, [
                'label' => 'Page contact',
                'class' => Page::class,
                'query_builder' => static function (PageRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.position', 'ASC')
                        ;
                },
                'choice_label' => 'title',
                'placeholder' => 'Sélectionnez la page de contact'
            ])
            ->add('policyPage', EntityType::class, [
                'label' => 'Page politique de confidentialité',
                'class' => Page::class,
                'query_builder' => static function (PageRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.position', 'ASC')
                        ;
                },
                'choice_label' => 'title',
                'placeholder' => 'Sélectionnez la page politique de confidentialité'
            ])
            ->add('analyticsCode', TextType::class, [
                'label' => 'Code UA (Google analytics)',
                'required' => false
            ])
            ->add('tagManagerCode', TextType::class, [
                'label' => 'Code GTM (Google Tag Manager)',
                'required' => false
            ])
            ->add('hasYoutubeVideos', CheckboxType::class, [
                'label' => 'Activer la gestion des vidéos Youtube ?',
                'required' => false
            ])
            ->add('soppCustomerId', TextType::class, [
                'label' => 'Identifiant client (sur mon-agence-web.io)',
                'required' => false
            ])
            ->add('idSirDataUser', TextType::class, [
                'label' => "Identifiant SirData (utilisateur)",
                'required' => false,
            ])
            ->add('idSirDataSite', TextType::class, [
                'label' => "Identifiant SirData (site)",
                'required' => false,
            ])
            ->add('scriptInjection', TextareaType::class, [
                'label' => "Injection de script (ex: Google Tag Manager, Google Analytics, ...)",
                'help' => "Le script sera injecté dans le head de toutes les pages du site après l'initialisation du service.",
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => RgpdOptions::class,]);
    }
}
