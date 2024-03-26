<?php

namespace Labstag\Form\Admin\Paragraph\Bookmark;

use Labstag\Entity\Paragraph\Bookmark\Category as BookmarkCategory;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends ParagraphAbstractTypeLib
{
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class' => BookmarkCategory::class,
            ]
        );
    }
}
