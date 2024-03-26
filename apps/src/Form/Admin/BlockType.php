<?php

namespace Labstag\Form\Admin;

use Labstag\Entity\Block;
use Labstag\Lib\BlockAbstractTypeLib;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends BlockAbstractTypeLib
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $block = $formBuilder->getData();
        if (!$block instanceof Block) {
            return;
        }

        $formType = $this->blockService->getTypeForm($block);
        $label    = $this->blockService->getName($block);
        $field    = $this->blockService->getEntityField($block);
        $show     = $this->blockService->isShow($block);
        $formBuilder->add(
            'title',
            TextType::class,
            [
                'label' => $this->translator->trans('block.title.label', [], 'admin.form'),
                'help'  => $this->translator->trans('block.title.help', [], 'admin.form'),
                'attr'  => [
                    'placeholder' => $this->translator->trans('block.title.placeholder', [], 'admin.form'),
                ],
            ]
        );
        $formBuilder->add(
            'region',
            ChoiceType::class,
            [
                'label'   => $this->translator->trans('block.region.label', [], 'admin.form'),
                'help'    => $this->translator->trans('block.region.help', [], 'admin.form'),
                'attr'    => [
                    'placeholder' => $this->translator->trans('block.region.placeholder', [], 'admin.form'),
                ],
                'choices' => $this->blockService->getRegions(),
            ]
        );
        if ((!is_null($formType) || is_null($field)) && $show) {
            $formBuilder->add(
                (string) $field,
                CollectionType::class,
                [
                    'label'         => $label,
                    'entry_type'    => $formType,
                    'entry_options' => ['label' => false],
                    'allow_add'     => false,
                    'allow_delete'  => false,
                ]
            );
        }

        $formBuilder->add(
            child: 'groupes',
            options: [
                'label' => $this->translator->trans('block.groupes.label', [], 'admin.form'),
                'help'  => $this->translator->trans('block.groupes.help', [], 'admin.form'),
                'attr'  => [
                    'placeholder' => $this->translator->trans('block.groupes.placeholder', [], 'admin.form'),
                ],
            ]
        );

        $formBuilder->add(
            'notinpages',
        );

        unset($options);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class' => Block::class,
            ]
        );
    }
}
