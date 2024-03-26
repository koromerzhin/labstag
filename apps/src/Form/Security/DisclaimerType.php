<?php

namespace Labstag\Form\Security;

use Labstag\Lib\AbstractTypeLib;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisclaimerType extends AbstractTypeLib
{
    public function buildForm(
        FormBuilderInterface $formBuilder,
        array $options
    ): void
    {
        $formBuilder->add(
            'confirm',
            CheckboxType::class,
            [
                'label'    => $this->translator->trans('disclaimer.confirm.label', [], 'security.form'),
                'help'     => $this->translator->trans('disclaimer.confirm.help', [], 'security.form'),
                'required' => false,
            ]
        );
        $formBuilder->add(
            'submit',
            SubmitType::class,
            [
                'label' => $this->translator->trans('disclaimer.submit.label', [], 'security.form'),
            ]
        );
        unset($options);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        // Configure your form options here
        $optionsResolver->setDefaults(
            ['csrf_token_id' => 'login']
        );
    }

    public function getBlockPrefix(): string
    {
        return 'disclaimer';
    }
}
