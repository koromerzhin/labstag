<?php

namespace Labstag\Form\Admin\Search;

use Labstag\Lib\SearchAbstractTypeLib;
use Labstag\Search\LibelleSearch;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibelleType extends SearchAbstractTypeLib
{
    /**
     * @inheritDoc
     */
    public function buildForm(
        FormBuilderInterface $formBuilder,
        array $options
    ): void
    {
        $this->addName($formBuilder);
        parent::buildForm($formBuilder, $options);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class'      => LibelleSearch::class,
                'csrf_protection' => false,
                'method'          => 'GET',
            ]
        );
    }
}
