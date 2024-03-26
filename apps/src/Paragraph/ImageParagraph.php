<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Attachment;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\History;
use Labstag\Entity\Layout;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph\Image;
use Labstag\Entity\Post;
use Labstag\Form\Admin\Paragraph\ImageType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

class ImageParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        if (!$entityParagraph instanceof Image) {
            return null;
        }

        $package    = new Package(new EmptyVersionStrategy());
        $attachment = $entityParagraph->getImage();
        $file       = ($attachment instanceof Attachment) ? $package->getUrl('/'.$attachment->getName()) : null;

        return [
            'paragraph'  => $entityParagraph,
            'attachment' => $file,
        ];
    }

    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return ['image'];
    }

    public function getEntity(): string
    {
        return Image::class;
    }

    public function getForm(): string
    {
        return ImageType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('image.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'image';
    }

    public function isShowForm(): bool
    {
        return true;
    }

    public function useIn(): array
    {
        return [
            Chapter::class,
            Edito::class,
            History::class,
            Layout::class,
            Memo::class,
            Page::class,
            Post::class,
        ];
    }
}
