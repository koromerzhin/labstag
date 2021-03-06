<?php

namespace Labstag\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class CoreTextareaType extends AbstractType
{
    public function buildView(
        FormView $view,
        FormInterface $form,
        array $options
    ): void
    {
        $attr = $options['attr'];
        if (!isset($attr['rows'])) {
            $attr['rows'] = 20;
        }

        $view->vars['attr'] = $attr;
        unset($form);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return TextareaType::class;
    }
}
