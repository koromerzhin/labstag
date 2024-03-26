<?php

namespace Labstag\Lib;

use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\History;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Render;
use Labstag\Interfaces\PublicInterface;
use Labstag\Queue\EnqueueMethod;
use Labstag\Service\BlockService;
use Labstag\Service\ErrorService;
use Labstag\Service\ParagraphService;
use Labstag\Service\RepositoryService;
use Labstag\Service\SessionService;
use Labstag\Service\UserMailService;
use Labstag\Service\WorkflowService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class EventListenerLib
{
    public function __construct(
        protected BlockService $blockService,
        protected UserPasswordHasherInterface $userPasswordHasher,
        protected ParagraphService $paragraphService,
        protected RepositoryService $repositoryService,
        protected UserMailService $userMailService,
        protected CacheInterface $cache,
        protected SessionService $sessionService,
        protected TranslatorInterface $translator,
        protected ErrorService $errorService,
        protected WorkflowService $workflowService,
        protected EnqueueMethod $enqueueMethod,
        protected ParameterBagInterface $parameterBag,
        protected LoggerInterface $logger
    )
    {
    }

    protected function verifMetas(
        PublicInterface $public
    ): void
    {
        $title = null;
        $metas = $public->getMetas();
        if (0 != count($metas)) {
            return;
        }

        $meta   = new Meta();
        $method = '';
        $title  = '';
        $this->verifMetasChapter($public, $method, $title);
        $this->verifMetasEdito($public, $method, $title);
        $this->verifMetasHistory($public, $method, $title);
        $this->verifMetasPage($public, $method, $title);
        $this->verifMetasPost($public, $method, $title);
        $this->verifMetasRender($public, $method, $title);
        if ('' != $method) {
            /** @var callable $callable */
            $callable = [
                $meta,
                $method,
            ];
            call_user_func($callable, $public);
        }

        $meta->setTitle($title);
    }

    private function verifMetasChapter(
        mixed $entity,
        string &$method,
        string &$title
    ): void
    {
        if (!$entity instanceof Chapter) {
            return;
        }

        $method = 'setChapter';
        $title  = $entity->getName();
    }

    private function verifMetasEdito(
        mixed $entity,
        string &$method,
        string &$title
    ): void
    {
        if (!$entity instanceof Edito) {
            return;
        }

        $method = 'setEdito';
        $title  = $entity->getTitle();
    }

    private function verifMetasHistory(
        mixed $entity,
        string &$method,
        string &$title
    ): void
    {
        if (!$entity instanceof History) {
            return;
        }

        $method = 'setHistory';
        $title  = $entity->getName();
    }

    private function verifMetasPage(
        mixed $entity,
        string &$method,
        string &$title
    ): void
    {
        if (!$entity instanceof Page) {
            return;
        }

        $method = 'setPage';
        $title  = $entity->getName();
    }

    private function verifMetasPost(
        mixed $entity,
        string &$method,
        string &$title
    ): void
    {
        if (!$entity instanceof Post) {
            return;
        }

        $method = 'setPost';
        $title  = $entity->getTitle();
    }

    private function verifMetasRender(
        mixed $entity,
        string &$method,
        string &$title
    ): void
    {
        if (!$entity instanceof Render) {
            return;
        }

        $method = 'setRender';
        $title  = $entity->getName();
    }
}
