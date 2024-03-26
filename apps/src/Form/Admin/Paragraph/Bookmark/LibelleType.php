<?php

namespace Labstag\Form\Admin\Paragraph\Bookmark;

use Labstag\Entity\Paragraph\Bookmark\Libelle as BookmarkLibelle;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibelleType extends ParagraphAbstractTypeLib
{
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class' => BookmarkLibelle::class,
            ]
        );
    }
}
