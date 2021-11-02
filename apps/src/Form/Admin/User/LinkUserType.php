<?php

namespace Labstag\Form\Admin\User;

use Labstag\Entity\LinkUser;
use Labstag\Entity\User;
use Labstag\Form\Admin\LinkType;
use Labstag\FormType\SearchableType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkUserType extends LinkType
{
    /**
     * @inheritdoc
     */
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        parent::buildForm($builder, $options);

        $builder->add(
            'refuser',
            SearchableType::class,
            [
                'label'    => $this->translator->trans('linkuser.refuser.label', [], 'admin.form'),
                'help'     => $this->translator->trans('linkuser.refuser.help', [], 'admin.form'),
                'multiple' => false,
                'class'    => User::class,
                'route'    => 'api_search_user',
                'attr'     => [
                    'placeholder' => $this->translator->trans('linkuser.refuser.placeholder', [], 'admin.form'),
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => LinkUser::class,
            ]
        );
    }
}
