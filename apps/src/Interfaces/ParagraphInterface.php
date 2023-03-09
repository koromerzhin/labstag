<?php

namespace Labstag\Interfaces;

use Labstag\Entity\Paragraph;

interface ParagraphInterface
{
    public function getParagraph(): ?Paragraph;

    public function setParagraph(?Paragraph $paragraph): self;
}
