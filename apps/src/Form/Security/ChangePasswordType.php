<?php

namespace Labstag\Form\Security;

use Labstag\Lib\AbstractTypeLib;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractTypeLib
{
    public function buildForm(
        FormBuilderInterface $formBuilder,
        array $options
    ): void
    {
        $first = [
            'label' => $this->translator->trans('changepassword.password.label', [], 'security.form'),
            'help'  => $this->translator->trans('changepassword.password.help', [], 'security.form'),
        ];
        $second = [
            'label' => $this->translator->trans('changepassword.repeatpassword.label', [], 'security.form'),
            'help'  => $this->translator->trans('changepassword.repeatpassword.help', [], 'security.form'),
        ];
        $formBuilder->add(
            'plainPassword',
            RepeatedType::class,
            [
                'type'           => PasswordType::class,
                'first_options'  => $first,
                'second_options' => $second,
            ]
        );
        $formBuilder->add(
            'submit',
            SubmitType::class,
            [
                'label' => $this->translator->trans('changepassword.password.label', [], 'security.form'),
            ]
        );
        unset($options);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        // Configure your form options here
        $optionsResolver->setDefaults(
            ['csrf_token_id' => 'changepassword']
        );
    }

    public function getBlockPrefix(): string
    {
        return 'change-password';
    }
}
