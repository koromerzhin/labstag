<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph\Text;
use Labstag\Form\Admin\Paragraph\TextType;
use Labstag\Lib\ParagraphLib;

class TextParagraph extends ParagraphLib
{
    public function getEntity()
    {
        return Text::class;
    }

    public function getForm()
    {
        return TextType::class;
    }

    public function getType()
    {
        return 'text';
    }

    public function show(Text $text, $content)
    {
        return $this->render(
            $this->getParagraphFile('text', $content),
            ['paragraph' => $text]
        );
    }
}