<?php

namespace Labstag\Form\Admin\Collections\User;

use Labstag\Entity\PhoneUser;
use Labstag\Form\Admin\PhoneType as AbstractPhoneType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneType extends AbstractPhoneType
{
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        // Configure your form options here
        $optionsResolver->setDefaults(
            [
                'data_class' => PhoneUser::class,
            ]
        );
    }
}
