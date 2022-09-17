<?php

namespace Labstag\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Entity\Bookmark;
use Labstag\Reader\UploadAnnotationReader;
use Labstag\Repository\BookmarkRepository;
use Labstag\Repository\UserRepository;
use Labstag\RequestHandler\AttachmentRequestHandler;
use Labstag\RequestHandler\BookmarkRequestHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\String\Slugger\AsciiSlugger;

class BookmarkService
{
    /**
     * @var int
     */
    final public const CLIENTNUMBER = 400;

    public function __construct(
        protected FileService $fileService,
        private readonly ErrorService $errorService,
        private readonly UploadAnnotationReader $uploadAnnotationReader,
        private readonly ContainerBagInterface $containerBag,
        private readonly BookmarkRequestHandler $bookmarkRequestHandler,
        protected UserRepository $userRepository,
        protected BookmarkRepository $bookmarkRepository
    )
    {
    }

    public function process(
        string $userid,
        string $url,
        string $name,
        string $icon,
        DateTime $dateTime
    )
    {
        $user     = $this->userRepository->find($userid);
        $bookmark = $this->bookmarkRepository->findOneBy(
            ['url' => $url]
        );
        if ($bookmark instanceof Bookmark) {
            return;
        }

        $bookmark = new bookmark();
        $old      = clone $bookmark;
        $bookmark->setRefuser($user);
        $bookmark->setUrl($url);
        $bookmark->setIcon($icon);
        $bookmark->setName($name);
        $bookmark->setPublished($dateTime);

        try {
            $headers = get_headers($url, 1);
            if (self::CLIENTNUMBER < substr((string) $headers[0], 9, 3)) {
                return;
            }

            $meta        = get_meta_tags($url);
            $description = $meta['description'] ?? null;
            $code        = 'twitter:description';
            $description = (is_null($description) && isset($meta[$code])) ? $meta[$code] : $description;
            $bookmark->setMetaDescription($description);
            $bookmark->setContent($description);
            $keywords = $meta['keywords'] ?? null;
            $bookmark->setMetaKeywords($keywords);
            $image = $meta['twitter:image'] ?? null;
            $image = (is_null($image) && isset($meta['og:image'])) ? $meta['og:image'] : $image;
            $this->upload($bookmark, $image);
            $this->bookmarkRepository->add($bookmark);
            $this->bookmarkRequestHandler->handle($old, $bookmark);
        } catch (Exception $exception) {
            $this->errorService->set($exception);
        }
    }

    protected function getParameter(string $name)
    {
        return $this->containerBag->get($name);
    }

    protected function upload(Bookmark $bookmark, $image)
    {
        if (is_null($image) || !$this->uploadAnnotationReader->isUploadable($bookmark)) {
            return;
        }

        // @var resource $finfo
        $finfo       = finfo_open(FILEINFO_MIME_TYPE);
        $annotations = $this->uploadAnnotationReader->getUploadableFields($bookmark);
        $asciiSlugger     = new AsciiSlugger();
        foreach ($annotations as $annotation) {
            $path     = $this->getParameter('file_directory').'/'.$annotation->getPath();
            $accessor = PropertyAccess::createPropertyAccessor();
            $title    = $accessor->getValue($bookmark, $annotation->getSlug());
            $slug     = $asciiSlugger->slug($title);

            try {
                $pathinfo = pathinfo((string) $image);
                $content  = file_get_contents($image);
                // @var resource $tmpfile
                $tmpfile = tmpfile();
                $data    = stream_get_meta_data($tmpfile);
                file_put_contents($data['uri'], $content);
                $file     = new UploadedFile(
                    $data['uri'],
                    $slug.'.'.$pathinfo['extension'],
                    (string) finfo_file($finfo, $data['uri']),
                    null,
                    true
                );
                $filename = $file->getClientOriginalName();
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                $file->move(
                    $path,
                    $filename
                );
                $file = $path.'/'.$filename;
            } catch (Exception $exception) {
                $this->errorService->set($exception);
            }

            $file       = $path.'/'.$filename;
            $attachment = $this->fileService->setAttachment($file);
            $accessor->setValue($bookmark, $annotation->getFilename(), $attachment);
        }
    }
}
