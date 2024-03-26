<?php

namespace Labstag\Paragraph;

use Embed\Embed;
use Embed\Extractor;
use Exception;
use finfo;
use Labstag\Annotation\UploadableField;
use Labstag\Entity\Attachment;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\History;
use Labstag\Entity\Layout;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Paragraph\Video;
use Labstag\Entity\Post;
use Labstag\Form\Admin\Paragraph\VideoType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Lib\RepositoryLib;
use Labstag\Repository\AttachmentRepository;
use Labstag\Repository\Paragraph\VideoRepository;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

class VideoParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        if (!$entityParagraph instanceof Video) {
            return null;
        }

        $extractor = $this->getData($entityParagraph);
        if (is_null($extractor)) {
            return null;
        }

        $package    = new Package(new EmptyVersionStrategy());
        $attachment = $entityParagraph->getImage();
        $image      = ($attachment instanceof Attachment) ? $package->getUrl('/'.$attachment->getName()) : null;

        if (is_null($image)) {
            /** @var UriInterface $extractorImage */
            $extractorImage = $extractor->image;
            $image          = $extractorImage->__toString();
        }

        $metas = $extractor->getMetas();

        $datas = $metas->get('og:video:url');
        $embed = (0 != (is_countable($datas) ? count($datas) : 0)) ? $datas[0] : null;

        return [
            'paragraph' => $entityParagraph,
            'image'     => $image,
            'data'      => $extractor,
            'embed'     => $embed,
        ];
    }

    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return ['video'];
    }

    public function getEntity(): string
    {
        return Video::class;
    }

    public function getForm(): string
    {
        return VideoType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('video.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'video';
    }

    public function isShowForm(): bool
    {
        return true;
    }

    public function setData(Paragraph $paragraph): void
    {
        /** @var VideoRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Video::class);
        /** @var AttachmentRepository $attachmentRepository */
        $attachmentRepository = $this->repositoryService->get(Attachment::class);
        $videos               = $paragraph->getVideos();
        /** @var Video $video */
        $video = $videos[0];
        $url   = (string) $video->getUrl();
        if ('' == $url) {
            return;
        }

        $slug = null;

        try {
            $embed = new Embed();
            $info  = $embed->get($url);
            /** @var UriInterface $infoimage */
            $infoimage    = $info->image;
            $image        = $infoimage->__toString();
            $title        = (string) $info->title;
            $asciiSlugger = new AsciiSlugger();
            $video->setTitle($title);
            $slug = (string) $asciiSlugger->slug($title);
            $video->setSlug($slug);
            $repositoryLib->save($video);
        } catch (Exception) {
            $image = '';
        }

        $attachment = null;
        if (!is_null($video->getImage())) {
            $attachment = $attachmentRepository->FindOneBy(
                [
                    'id'        => $video->getImage()->getId(),
                    'deletedAt' => null,
                ]
            );
        }

        if ('' == $image || $attachment instanceof Attachment) {
            return;
        }

        $annotations = $this->uploadAnnotationReader->getUploadableFields($video);
        foreach ($annotations as $annotation) {
            /** @var UploadableField $annotation */
            $this->setDataAnnotation($annotation, $image, $video, $repositoryLib, $slug);
        }
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

    private function getData(Video $video): ?Extractor
    {
        $url = (string) $video->getUrl();
        if ('' == $url) {
            return null;
        }

        $embed = new Embed();

        return $embed->get($url);
    }

    private function setDataAnnotation(
        UploadableField $uploadableField,
        string $image,
        Video $video,
        RepositoryLib $serviceEntityRepositoryLib,
        string $slug
    ): void
    {
        /** @var finfo $finfo */
        $finfo         = finfo_open(FILEINFO_MIME_TYPE);
        $fileDirectory = $this->getParameter('file_directory');
        if (!is_string($fileDirectory)) {
            return;
        }

        try {
            $path    = $fileDirectory.'/'.$uploadableField->getPath();
            $content = file_get_contents($image);
            /** @var resource $tmpfile */
            $tmpfile = tmpfile();
            $data    = stream_get_meta_data($tmpfile);
            file_put_contents($data['uri'], $content);
            $file = new UploadedFile(
                $data['uri'],
                $slug.'.jpg',
                (string) finfo_file($finfo, $data['uri']),
                null,
                true
            );
            $clientOriginalName = $file->getClientOriginalName();
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $file->move(
                $path,
                $clientOriginalName
            );
            $file       = $path.'/'.$clientOriginalName;
            $attachment = $this->fileService->setAttachment($file);
            $serviceEntityRepositoryLib->save($attachment);
            $video->setImage($attachment);
            $serviceEntityRepositoryLib->save($video);
        } catch (Exception $exception) {
            $this->errorService->set($exception);
            echo $exception->getMessage();
        }
    }
}
