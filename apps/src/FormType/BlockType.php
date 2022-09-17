<?php

namespace Labstag\FormType;

use Labstag\Service\BlockService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{
    public function __construct(
        protected BlockService $blockService
    )
    {
    }

    public function buildView(
        FormView $formView,
        FormInterface $form,
        array $options
    ): void
    {
        unset($formView, $form, $options);
    }

    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults(
            [
                'placeholder' => 'Choisir le block',
                'choices'     => $this->blockService->getAll(),
                'add'         => null,
                'edit'        => null,
                'delete'      => null,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
