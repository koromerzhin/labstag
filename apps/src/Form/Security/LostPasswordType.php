<?php

namespace Labstag\Form\Security;

use Labstag\Lib\AbstractTypeLib;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LostPasswordType extends AbstractTypeLib
{
    public function buildForm(
        FormBuilderInterface $formBuilder,
        array $options
    ): void
    {
        $formBuilder->add(
            'value',
            TextType::class,
            [
                'label'    => $this->translator->trans('lostpassword.value.label', [], 'security.form'),
                'help'     => $this->translator->trans('lostpassword.value.help', [], 'security.form'),
                'required' => false,
                'attr'     => [
                    'placeholder' => $this->translator->trans('lostpassword.value.placeholder', [], 'security.form'),
                ],
            ]
        );
        $formBuilder->add(
            'submit',
            SubmitType::class,
            [
                'label' => $this->translator->trans('lostpassword.submit.label', [], 'security.form'),
            ]
        );
        unset($options);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        // Configure your form options here
        $optionsResolver->setDefaults(
            ['csrf_token_id' => 'lostpassword']
        );
    }

    public function getBlockPrefix(): string
    {
        return 'lost-password';
    }
}
