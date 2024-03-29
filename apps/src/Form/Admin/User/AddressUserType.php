<?php

namespace Labstag\Form\Admin\User;

use Labstag\Entity\AddressUser;
use Labstag\Entity\User;
use Labstag\Form\Admin\AddressType;
use Labstag\FormType\SearchableType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressUserType extends AddressType
{
    /**
     * @inheritDoc
     */
    public function buildForm(
        FormBuilderInterface $formBuilder,
        array $options
    ): void
    {
        parent::buildForm($formBuilder, $options);
        $formBuilder->add(
            'refuser',
            SearchableType::class,
            [
                'label'    => $this->translator->trans('addressuser.refuser.label', [], 'admin.form'),
                'help'     => $this->translator->trans('addressuser.refuser.help', [], 'admin.form'),
                'multiple' => false,
                'class'    => User::class,
                'route'    => 'api_search_user',
                'attr'     => [
                    'placeholder' => $this->translator->trans('addressuser.refuser.placeholder', [], 'admin.form'),
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class' => AddressUser::class,
            ]
        );
    }
}
