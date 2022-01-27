<?php

namespace Labstag\Form\Admin;

use Labstag\Lib\AbstractTypeLib;
use Symfony\Component\Form\Extension\Core\Type\EmailType as TypeEmailType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class EmailType extends AbstractTypeLib
{
    /**
     * @inheritDoc
     */
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void
    {
        unset($options);
        $builder->add(
            'address',
            TypeEmailType::class,
            [
                'label' => $this->translator->trans('email.address.label', [], 'admin.form'),
                'help'  => $this->translator->trans('email.address.help', [], 'admin.form'),
                'attr'  => [
                    'placeholder' => $this->translator->trans('email.address.placeholder', [], 'admin.form'),
                ],
            ]
        );
    }
}
