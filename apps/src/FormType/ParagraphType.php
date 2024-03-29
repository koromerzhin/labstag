<?php

namespace Labstag\FormType;

use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Lib\FormTypeLib;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParagraphType extends FormTypeLib
{
    public function buildView(
        FormView $formView,
        FormInterface $form,
        array $options
    ): void
    {
        /** @var FormInterface $parent */
        $parent = $form->getParent();
        $entity = $parent->getData();
        if (!$entity instanceof EntityFrontInterface) {
            return;
        }

        $paragraphs = $this->paragraphService->getAll($entity);
        $choices    = [];
        foreach ($paragraphs as $name => $type) {
            $choices[$type] = new ChoiceView('', (string) $type, (string) $name);
        }

        $formView->vars['label'] = 'Paragraphs';
        if (!is_null($entity->getId()) && is_string($options['add'])) {
            $formView->vars['urlAdd'] = $this->router->generate($options['add'], ['id' => $entity->getId()]);
        }

        $formView->vars['paragraphs'] = $entity->getParagraphs();
        $formView->vars['urlEdit']    = $options['edit'];
        $formView->vars['urlDelete']  = $options['delete'];
        $formView->vars['choices']    = $choices;
        $formView->vars['attr']['is'] = 'select-paragraph';
        unset($form);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'placeholder' => 'Choisir le paragraphe',
                'choices'     => [],
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
