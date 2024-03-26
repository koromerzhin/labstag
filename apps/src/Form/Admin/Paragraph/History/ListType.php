<?php

namespace Labstag\Form\Admin\Paragraph\History;

use Labstag\Entity\Paragraph\History\Liste as HistoryListe;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListType extends ParagraphAbstractTypeLib
{
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class' => HistoryListe::class,
            ]
        );
    }
}
