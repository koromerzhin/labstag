<?php

namespace Labstag\Form\Admin\Paragraph\History;

use Labstag\Entity\Paragraph\History\User as HistoryUser;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends ParagraphAbstractTypeLib
{
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class' => HistoryUser::class,
            ]
        );
    }
}
