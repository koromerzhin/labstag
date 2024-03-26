<?php

namespace Labstag\Form\Admin;

use Labstag\Form\Admin\Collections\Form\ButtonsFieldsType;
use Labstag\Form\Admin\Collections\Form\ChoiceFieldsType;
use Labstag\Form\Admin\Collections\Form\CollectionFieldsType;
use Labstag\Form\Admin\Collections\Form\DateAndTimeFieldsType;
use Labstag\Form\Admin\Collections\Form\ExtraFieldsFieldsType;
use Labstag\Form\Admin\Collections\Form\HiddenFieldsType;
use Labstag\Form\Admin\Collections\Form\OtherFieldsType;
use Labstag\Form\Admin\Collections\Form\TextFieldsType;
use Labstag\FormType\MinMaxCollectionType;
use Labstag\Lib\AbstractTypeLib;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormType extends AbstractTypeLib
{
    /**
     * @inheritDoc
     */
    public function buildForm(
        FormBuilderInterface $formBuilder,
        array $options
    ): void
    {
        unset($options);

        $formBuilder->add(
            'buttons',
            CollectionType::class,
            [
                'entry_type' => ButtonsFieldsType::class,
            ]
        );
        $formBuilder->add(
            'choice',
            CollectionType::class,
            [
                'entry_type' => ChoiceFieldsType::class,
            ]
        );
        $formBuilder->add(
            'dateandtime',
            CollectionType::class,
            [
                'entry_type' => DateAndTimeFieldsType::class,
            ]
        );
        $formBuilder->add(
            'extra',
            CollectionType::class,
            [
                'entry_type' => ExtraFieldsFieldsType::class,
            ]
        );
        $formBuilder->add(
            'hidden',
            CollectionType::class,
            [
                'entry_type' => HiddenFieldsType::class,
            ]
        );
        $formBuilder->add(
            'other',
            MinMaxCollectionType::class,
            [
                'entry_type' => OtherFieldsType::class,
            ]
        );
        $formBuilder->add(
            'text',
            MinMaxCollectionType::class,
            [
                'entry_type' => TextFieldsType::class,
            ]
        );
        $formBuilder->add(
            'collection',
            MinMaxCollectionType::class,
            [
                'entry_type'   => CollectionFieldsType::class,
                'allow_add'    => true,
                'allow_delete' => true,
            ]
        );
        $this->addPlainPassword($formBuilder);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            []
        );
    }
}
