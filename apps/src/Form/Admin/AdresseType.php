<?php

namespace Labstag\Form\Admin;

use Labstag\Entity\Adresse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AdresseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void
    {
        unset($options);
        $builder->add('rue');
        $builder->add('country');
        $builder->add('zipcode');
        $builder->add(
            'ville',
            TextType::class,
            [
                'attr' => [
                    'is' => 'adresse_town'
                ]
            ]
        );
        $builder->add('gps');
        $builder->add('type');
        $builder->add('pmr');
    }
}
