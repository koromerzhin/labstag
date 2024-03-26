<?php

namespace Labstag\Interfaces;

use Labstag\Entity\Paragraph;
use Symfony\Component\HttpFoundation\Response;

interface ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed;

    public function getClassCSS(array $dataClass, EntityParagraphInterface $entityParagraph): array;

    public function getEntity(): string;

    public function getForm(): string;

    public function getName(): string;

    public function getType(): string;

    public function isShowForm(): bool;

    public function setData(Paragraph $paragraph): void;

    public function template(EntityParagraphInterface $entityParagraph): array;

    public function twig(EntityParagraphInterface $entityParagraph): string;

    public function useIn(): array;

    public function view(string $twig, array $parameters = []): ?Response;
}
