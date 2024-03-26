<?php

namespace Labstag\Twig;

use Labstag\Entity\Paragraph;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Lib\ExtensionLib;
use Twig\TwigFilter;

class ParagraphExtension extends ExtensionLib
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'paragraph_name',
                fn (string $code): string => $this->getParagraphName($code)
            ),
            new TwigFilter(
                'paragraph_id',
                fn (EntityParagraphInterface $entityParagraph): string => $this->getParagraphId($entityParagraph)
            ),
            new TwigFilter(
                'paragraph_class',
                fn (EntityParagraphInterface $entityParagraph): string => $this->getParagraphClass($entityParagraph)
            ),
        ];
    }

    public function getParagraphClass(EntityParagraphInterface $entityParagraph): string
    {
        /** @var Paragraph $paragraph */
        $paragraph = $entityParagraph->getParagraph();
        $dataClass = [
            'paragraph-'.$paragraph->getType(),
        ];

        $code = $paragraph->getBackground();
        if (null !== $code && '' !== $code) {
            $dataClass[] = 'm--background-'.$code;
        }

        $code = $paragraph->getColor();
        if (null !== $code && '' !== $code) {
            $dataClass[] = 'm--theme-'.$code;
        }

        $dataClass = $this->paragraphService->getClassCSS($dataClass, $paragraph);

        return implode(' ', $dataClass);
    }

    public function getParagraphId(EntityParagraphInterface $entityParagraph): string
    {
        /** @var Paragraph $paragraph */
        $paragraph = $entityParagraph->getParagraph();

        return 'paragraph-'.$paragraph->getType().'-'.$paragraph->getId();
    }

    public function getParagraphName(string $code): string
    {
        return $this->paragraphService->getNameByCode($code);
    }
}
