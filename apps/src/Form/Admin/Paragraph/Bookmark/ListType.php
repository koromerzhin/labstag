<?php

namespace Labstag\Form\Admin\Paragraph\Bookmark;

use Labstag\Entity\Paragraph\Bookmark\Liste as BookmarkListe;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListType extends ParagraphAbstractTypeLib
{
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class' => BookmarkListe::class,
            ]
        );
    }
}
