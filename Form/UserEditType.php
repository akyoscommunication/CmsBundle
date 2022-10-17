<?php

namespace Akyos\CmsBundle\Form;

use Akyos\CmsBundle\Entity\User;
use Akyos\FileManagerBundle\Form\Type\FileManagerType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserEditType extends AbstractType
{
    private AuthorizationCheckerInterface $authorizationChecker;

    private ContainerInterface $container;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, ContainerInterface $container)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = $this->container->getParameter('user_roles');
        if (!$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            unset($roles['Super Admin']);
        }
        if (!$this->authorizationChecker->isGranted('ROLE_AKYOS')) {
            unset($roles['Akyos']);
        }

        $builder
            ->add('email', EmailType::class, [
                'label' => "E-mail",
                'help' => "Renseignez l'email de l'utilisateur"
            ])
            ->add('roles', ChoiceType::class, [
                'label' => "Rôle de l'utilisateur",
                'help' => "En fonction de son rôle, l'utilisateur aura accès à différentes fonctionnalités.",
                'choices' => $roles,
                'multiple' => true,
                'expanded' => false,
                'required' => true,
                'attr' => [
                    'class' => 'js-select2',
                ]
            ])
            ->add('image', FileManagerType::class, ['label' => 'Image de profil',]);

        if ($options['hasPasswordField']) {
            $builder->add('modifyPassword', CheckboxType::class, [
                'label' => 'Modifier le mot de passe ?',
                'required' => false,
                'mapped' => false,
                'data' => $options['modifyPassword']
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $modifyPassword = $form->get('modifyPassword')->getData();
            if ($modifyPassword) {
                $this->addPasswordField($form);
            }
        });
        }
    }

    public function addPasswordField(FormInterface $form) {
        $form->add('password', PasswordType::class, [
            'label' => "Mot de passe",
            'help' => "Renseignez un mot de passe pour l'utilisateur.",
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class, 'hasPasswordField' => false, 'modifyPassword' => false, 'allow_extra_fields' => true]);
    }
}
