<?php

namespace Labstag\Form\Admin\Paragraph;

use Labstag\Entity\Paragraph\Form;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormType extends ParagraphAbstractTypeLib
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder->add(
            'form',
            ChoiceType::class,
            [
                'required' => false,
                'label'    => 'form',
                'choices'  => $this->formService->getForm(),
            ]
        );
        unset($options);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class' => Form::class,
            ]
        );
    }
}
