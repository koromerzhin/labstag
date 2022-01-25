<?php

namespace Labstag\Lib;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class SearchAbstractTypeLib extends AbstractType
{
    public function __construct(protected Registry $workflows, protected TranslatorInterface $translator)
    {
    }

    protected function addName($builder, $label, $help, $placeholder)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'required' => false,
                'label'    => $label,
                'help'     => $help,
                'attr'     => ['placeholder' => $placeholder],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void
    {
        $builder->add(
            'limit',
            NumberType::class,
            [
                'required' => false,
                'label'    => $this->translator->trans('form.limit.label', [], 'admin.search.form'),
                'help'     => $this->translator->trans('form.limit.help', [], 'admin.search.form'),
                'attr'     => [
                    'placeholder' => $this->translator->trans('form.limit.placeholder', [], 'admin.search.form'),
                ],
            ]
        );
        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => $this->translator->trans('form.submit', [], 'admin.search.form'),
                'attr'  => ['name' => ''],
            ]
        );
        $builder->add(
            'reset',
            ResetType::class,
            [
                'label' => $this->translator->trans('form.reset', [], 'admin.search.form'),
                'attr'  => ['name' => ''],
            ]
        );
        unset($options);
    }

    protected function showState(
        $builder,
        $entityclass,
        $label,
        $help,
        $placeholder
    )
    {
        $workflow   = $this->workflows->get($entityclass);
        $definition = $workflow->getDefinition();
        $places     = $definition->getPlaces();
        $builder->add(
            'etape',
            ChoiceType::class,
            [
                'required' => false,
                'label'    => $label,
                'help'     => $help,
                'choices'  => $places,
                'attr'     => ['placeholder' => $placeholder],
            ]
        );
    }
}
