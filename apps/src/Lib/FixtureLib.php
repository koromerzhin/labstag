<?php

namespace Labstag\Lib;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;
use Faker\Generator;
use finfo;
use Labstag\Annotation\UploadableField;
use Labstag\DataFixtures\CategoryFixtures;
use Labstag\DataFixtures\DataFixtures;
use Labstag\DataFixtures\LibelleFixtures;
use Labstag\DataFixtures\UserFixtures;
use Labstag\Entity\Bookmark;
use Labstag\Entity\Groupe;
use Labstag\Entity\Libelle;
use Labstag\Entity\Meta;
use Labstag\Entity\Post;
use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Interfaces\EntityInterface;
use Labstag\Queue\EnqueueMethod;
use Labstag\Reader\UploadAnnotationReader;
use Labstag\Service\BlockService;
use Labstag\Service\ErrorService;
use Labstag\Service\FileService;
use Labstag\Service\GuardService;
use Labstag\Service\InstallService;
use Labstag\Service\ParagraphService;
use Labstag\Service\RepositoryService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Mmo\Faker\LoremSpaceProvider;
use Mmo\Faker\PicsumProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Environment;

abstract class FixtureLib extends Fixture
{
    /**
     * @var int
     */
    protected const NUMBER_ADRESSE = 25;

    /**
     * @var int
     */
    protected const NUMBER_BOOKMARK = 25;

    /**
     * @var int
     */
    protected const NUMBER_CATEGORY = 50;

    /**
     * @var int
     */
    protected const NUMBER_CHAPTER = 25;

    /**
     * @var int
     */
    protected const NUMBER_EDITO = 25;

    /**
     * @var int
     */
    protected const NUMBER_EMAIL = 25;

    /**
     * @var int
     */
    protected const NUMBER_HISTORY = 25;

    /**
     * @var int
     */
    protected const NUMBER_LIBELLE = 10;

    /**
     * @var int
     */
    protected const NUMBER_LINK = 25;

    /**
     * @var int
     */
    protected const NUMBER_NOTEINTERNE = 25;

    /**
     * @var int
     */
    protected const NUMBER_PHONE = 25;

    /**
     * @var int
     */
    protected const NUMBER_POST = 100;

    /**
     * @var int
     */
    protected const NUMBER_TEMPLATES = 10;

    public function __construct(
        protected EnqueueMethod $enqueueMethod,
        protected RepositoryService $repositoryService,
        protected WorkflowService $workflowService,
        protected FileService $fileService,
        protected UserService $userService,
        protected ErrorService $errorService,
        protected ParagraphService $paragraphService,
        protected LoggerInterface $logger,
        protected ContainerBagInterface $containerBag,
        protected UploadAnnotationReader $uploadAnnotationReader,
        protected InstallService $installService,
        protected GuardService $guardService,
        protected Environment $twigEnvironment,
        protected BlockService $blockService,
        protected CacheInterface $cache
    )
    {
    }

    public function getDependenciesBookmarkPost(): array
    {
        return [
            DataFixtures::class,
            UserFixtures::class,
            LibelleFixtures::class,
            CategoryFixtures::class,
        ];
    }

    protected function addParagraphs(
        EntityFrontInterface $entityFront,
        array $paragraphs,
        ObjectManager $objectManager
    ): void
    {
        unset($objectManager);
        foreach ($paragraphs as $paragraph) {
            $name   = is_array($paragraph) ? $paragraph['name'] : $paragraph;
            $config = is_array($paragraph) ? $paragraph['config'] : [];
            $this->paragraphService->add($entityFront, $name, $config);
        }
    }

    protected function getGroupe(array $groupes, string $code): ?Groupe
    {
        foreach ($groupes as $groupe) {
            if ($groupe->getCode() == $code) {
                return $groupe;
            }
        }

        return null;
    }

    protected function getStatesData(): array
    {
        return [
            ['submit'],
            [
                'submit',
                'relire',
            ],
            [
                'submit',
                'relire',
                'corriger',
            ],
            [
                'submit',
                'relire',
                'publier',
            ],
            [
                'submit',
                'relire',
                'rejeter',
            ],
        ];
    }

    protected function loadForeach(
        int $number,
        string $method,
        ObjectManager $objectManager
    ): void
    {
        $generator = $this->setFaker();
        $statesTab = $this->getStatesData();
        for ($index = 0; $index < $number; ++$index) {
            $stateId = array_rand($statesTab);
            $states  = $statesTab[$stateId];
            /** @var callable $callable */
            $callable = [
                $this,
                $method,
            ];
            call_user_func_array($callable, [$generator, $index, $states, $objectManager]);
        }
    }

    protected function loadForeachUser(
        int $number,
        string $method,
        ObjectManager $objectManager
    ): void
    {
        $generator = $this->setFaker();
        $users     = $this->installService->getData('user');
        for ($index = 0; $index < $number; ++$index) {
            $indexUser = $generator->numberBetween(0, (is_countable($users) ? count($users) : 0) - 1);
            $user      = $this->getReference('user_'.$indexUser);
            /** @var callable $callable */
            $callable = [
                $this,
                $method,
            ];
            call_user_func_array($callable, [$generator, $user, $objectManager]);
        }
    }

    protected function setFaker(): Generator
    {
        $generator = Factory::create('fr_FR');
        $generator->addProvider(new PicsumProvider($generator));
        $generator->addProvider(new LoremSpaceProvider($generator));

        return $generator;
    }

    protected function setLibelles(
        Generator $generator,
        ?Bookmark $bookmark = null,
        ?Post $post = null
    ): void
    {
        if (1 != random_int(0, 1) || (is_null($bookmark) && is_null($post))) {
            return;
        }

        $entity = is_null($bookmark) ? $post : $bookmark;
        if (is_null($entity)) {
            return;
        }

        $nbr = $generator->numberBetween(0, self::NUMBER_LIBELLE - 1);
        for ($i = 0; $i < $nbr; ++$i) {
            $indexLibelle = $generator->numberBetween(0, self::NUMBER_LIBELLE - 1);
            /** @var Libelle $libelle */
            $libelle = $this->getReference('libelle_'.$indexLibelle);
            $entity->addLibelle($libelle);
        }
    }

    protected function setMeta(Meta $meta): void
    {
        $generator = $this->setFaker();
        $meta->setTitle($generator->unique()->colorName());
        $meta->setDescription($generator->unique()->sentence());

        $keywords = $generator->unique()->words(random_int(1, 5), true);
        if (is_string($keywords)) {
            $meta->setKeywords($keywords);
        }
    }

    protected function upload(EntityInterface $entity, Generator $generator): void
    {
        /** @var finfo $finfo */
        $finfo        = finfo_open(FILEINFO_MIME_TYPE);
        $annotations  = $this->uploadAnnotationReader->getUploadableFields($entity);
        $asciiSlugger = new AsciiSlugger();
        foreach ($annotations as $annotation) {
            /** @var UploadableField $annotation */
            $path     = $this->containerBag->get('file_directory').'/'.$annotation->getPath();
            $accessor = PropertyAccess::createPropertyAccessor();
            $slug     = $annotation->getSlug();
            if (!is_string($slug)) {
                continue;
            }

            $title = $accessor->getValue($entity, $slug);
            if (!is_string($title)) {
                continue;
            }

            $slug = $asciiSlugger->slug($title);

            try {
                /** @var PicsumProvider $generator */
                $image = $generator->picsum(
                    width: 640,
                    height: 480,
                    fullPath: true
                );
                if (!empty($image)) {
                    $content = file_get_contents($image);
                    /** @var resource $tmpfile */
                    $tmpfile = tmpfile();
                    $data    = stream_get_meta_data($tmpfile);
                    file_put_contents($data['uri'], $content);
                    $file = new UploadedFile(
                        path: $data['uri'],
                        originalName: $slug.'.jpg',
                        mimeType: (string) finfo_file($finfo, $data['uri']),
                        test: true
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
                }
            } catch (Exception $exception) {
                $this->errorService->set($exception);
                echo $exception->getMessage();
            }

            if (isset($file)) {
                $attachment = $this->fileService->setAttachment($file);
                $filename   = $annotation->getFilename();
                if (!is_string($filename)) {
                    continue;
                }

                $accessor->setValue($entity, $filename, $attachment);
            }
        }
    }
}
