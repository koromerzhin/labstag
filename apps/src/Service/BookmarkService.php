<?php

namespace Labstag\Service;

use DateTime;
use Exception;
use Labstag\Entity\Bookmark;
use Labstag\Reader\UploadAnnotationReader;
use Labstag\Repository\BookmarkRepository;
use Labstag\Repository\UserRepository;
use Labstag\RequestHandler\BookmarkRequestHandler;
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
    ): void
    {
        $user = $this->userRepository->find($userid);
        $bookmark = $this->bookmarkRepository->findOneBy(
            ['url' => $url]
        );
        if ($bookmark instanceof Bookmark) {
            return;
        }

        $bookmark = new bookmark();
        $old = clone $bookmark;
        $bookmark->setRefuser($user);
        $bookmark->setUrl($url);
        $bookmark->setIcon($icon);
        $bookmark->setName($name);
        $bookmark->setPublished($dateTime);

        try {
            $headers = get_headers($url, true);
            if (self::CLIENTNUMBER < substr((string) $headers[0], 9, 3)) {
                return;
            }

            $meta = get_meta_tags($url);
            $description = $meta['description'] ?? null;
            $code = 'twitter:description';
            $description = (is_null($description) && isset($meta[$code])) ? $meta[$code] : $description;
            $bookmark->setContent($description);
            $image = $meta['twitter:image'] ?? null;
            $image = (is_null($image) && isset($meta['og:image'])) ? $meta['og:image'] : $image;
            $this->upload($bookmark, $image);
            $this->bookmarkRepository->add($bookmark);
            $this->bookmarkRequestHandler->handle($old, $bookmark);
        } catch (Exception $exception) {
            $this->errorService->set($exception);
        }
    }

    protected function upload(
        Bookmark $bookmark,
        string $image
    ): void
    {
        /** @var  resource $finfo */
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $annotations = $this->uploadAnnotationReader->getUploadableFields($bookmark);
        $asciiSlugger = new AsciiSlugger();
        foreach ($annotations as $annotation) {
            $path = $this->containerBag->get('file_directory').'/'.$annotation->getPath();
            $accessor = PropertyAccess::createPropertyAccessor();
            $title = $accessor->getValue($bookmark, $annotation->getSlug());
            $slug = $asciiSlugger->slug($title);

            try {
                $pathinfo = pathinfo((string) $image);
                $content = file_get_contents($image);
                /** @var  resource $tmpfile */
                $tmpfile = tmpfile();
                $data = stream_get_meta_data($tmpfile);
                file_put_contents($data['uri'], $content);
                $file = new UploadedFile(
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

            if (isset($file)) {
                $attachment = $this->fileService->setAttachment($file);
                $accessor->setValue($bookmark, $annotation->getFilename(), $attachment);
            }
        }
    }
}
