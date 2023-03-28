<?php

namespace Labstag\Form\Admin\Paragraph\Post;

use Labstag\Entity\Paragraph\Post\Category;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends ParagraphAbstractTypeLib
{
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class' => Category::class,
            ]
        );
    }
}
