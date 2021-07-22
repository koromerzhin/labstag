<?php

namespace Labstag\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class TrashService
{

    protected CsrfTokenManagerInterface $csrfTokenManager;

    protected ManagerRegistry $manager;

    public function __construct(
        ManagerRegistry $manager,
        CsrfTokenManagerInterface $csrfTokenManager
    )
    {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->manager          = $manager;
    }

    public function all()
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__.'/../Repository');
        $data = [];
        foreach ($finder as $file) {
            $repositoryFile = 'Labstag\\Repository\\'.$file->getFilenameWithoutExtension();
            $isTrashable    = $this->isTrashable($repositoryFile);
            if ($isTrashable) {
                $entity     = str_replace(
                    'Repository',
                    '',
                    'Labstag\\Entity\\'.$file->getFilenameWithoutExtension()
                );
                $repository = $this->manager->getRepository($entity);
                $trash      = $repository->findTrashForAdmin();
                if (0 == count($trash)) {
                    continue;
                }

                $data[] = [
                    'name'       => strtolower(
                        str_replace(
                            'Repository',
                            '',
                            $file->getFilenameWithoutExtension()
                        )
                    ),
                    'properties' => $this->getProperties($repositoryFile),
                    'entity'     => $entity,
                    'total'      => count($trash),
                    'token'      => $this->csrfTokenManager->getToken('empty')->getValue(),
                ];
            }
        }

        return $data;
    }

    public function getProperties(string $repository)
    {
        $reader     = new AnnotationReader();
        $properties = [];
        if (!$this->isTrashable($repository)) {
            return $properties;
        }

        $reflection = $this->setReflection($repository);
        $properties = $reader->getClassAnnotations($reflection);
        $properties = $properties[0];

        return $properties;
    }

    public function isTrashable(string $repository): bool
    {
        $reader     = new AnnotationReader();
        $reflection = $this->setReflection($repository);
        $annotation = $reader->getClassAnnotation($reflection, Trashable::class);

        return !is_null($annotation);
    }

    protected function setReflection(string $repository): ReflectionClass
    {
        return new ReflectionClass($repository);
    }
}
