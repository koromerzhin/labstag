<?php

namespace Labstag\Form\Security;

use Labstag\Lib\AbstractTypeLib;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractTypeLib
{
    public function buildForm(
        FormBuilderInterface $formBuilder,
        array $options
    ): void
    {
        $formBuilder->add(
            'username',
            TextType::class,
            [
                'label' => $this->translator->trans('login.username.label', [], 'security.form'),
                'help'  => $this->translator->trans('login.username.help', [], 'security.form'),
                'attr'  => [
                    'placeholder' => $this->translator->trans('login.username.placeholder', [], 'security.form'),
                ],
            ]
        );
        $formBuilder->add(
            'password',
            PasswordType::class,
            [
                'label' => $this->translator->trans('login.password.label', [], 'security.form'),
                'help'  => $this->translator->trans('login.password.help', [], 'security.form'),
            ]
        );

        $formBuilder->add(
            'remember_me',
            CheckboxType::class,
            [
                'label'    => $this->translator->trans('login.remember_me.label', [], 'security.form'),
                'help'     => $this->translator->trans('login.remember_me.help', [], 'security.form'),
                'required' => false,
            ]
        );
        $formBuilder->add(
            'submit',
            SubmitType::class,
            [
                'label' => $this->translator->trans('login.submit.label', [], 'security.form'),
            ]
        );
        unset($options);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        // Configure your form options here
        $optionsResolver->setDefaults(
            [
                'csrf_field_name' => '_csrf_token',
                'csrf_token_id'   => 'authenticate',
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'login';
    }
}
