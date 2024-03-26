<?php

namespace Labstag\Form\Admin\Paragraph\Post;

use Labstag\Entity\Paragraph\Post\Liste as PostListe;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListType extends ParagraphAbstractTypeLib
{
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class' => PostListe::class,
            ]
        );
    }
}
