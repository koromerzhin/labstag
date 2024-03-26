<?php

namespace Labstag\Lib;

use Knp\Component\Pager\PaginatorInterface;
use Labstag\Entity\Paragraph;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Reader\UploadAnnotationReader;
use Labstag\Service\ErrorService;
use Labstag\Service\FileService;
use Labstag\Service\FormService;
use Labstag\Service\ParagraphService;
use Labstag\Service\RepositoryService;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class ParagraphLib extends AbstractController
{

    protected array $template = [];

    public function __construct(
        protected CacheInterface $cache,
        protected FileService $fileService,
        protected UploadAnnotationReader $uploadAnnotationReader,
        protected ErrorService $errorService,
        protected PaginatorInterface $paginator,
        protected TranslatorInterface $translator,
        protected MailerInterface $mailer,
        protected Environment $twigEnvironment,
        protected ParagraphService $paragraphService,
        protected RequestStack $requestStack,
        protected FormService $formService,
        protected RepositoryService $repositoryService
    )
    {
    }

    public function getClassCSS(
        array $dataClass,
        EntityParagraphInterface $entityParagraph
    ): array
    {
        unset($entityParagraph);

        return $dataClass;
    }

    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return [];
    }

    public function setData(Paragraph $paragraph): void
    {
        unset($paragraph);
    }

    public function template(EntityParagraphInterface $entityParagraph): array
    {
        return $this->showTemplateFile($this->getCode($entityParagraph));
    }

    public function twig(EntityParagraphInterface $entityParagraph): string
    {
        return $this->getTemplateFile($this->getCode($entityParagraph));
    }

    public function view(string $twig, array $parameters = []): ?Response
    {
        return $this->render(
            $twig,
            $parameters
        );
    }

    protected function getTemplateData(array $types): array
    {
        $code = md5(serialize($types));
        if (isset($this->template[$code])) {
            return $this->template[$code];
        }

        $loader   = $this->twigEnvironment->getLoader();
        $htmltwig = '.html.twig';
        $files    = array_map(
            static fn ($type): string => 'paragraph/'.$type.$htmltwig,
            $types
        );
        $files[] = 'paragraph/default'.$htmltwig;

        $view = end($files);

        foreach ($files as $file) {
            if (!$loader->exists($file)) {
                continue;
            }

            $view = $file;

            break;
        }

        $this->template[$code] = [
            'hook'  => 'paragraph',
            'types' => $types,
            'files' => $files,
            'view'  => $view,
        ];

        return $this->template[$code];
    }

    protected function getTemplateFile(array $types): string
    {
        $data = $this->getTemplateData($types);

        return $data['view'];
    }

    protected function showTemplateFile(array $types): array
    {
        $data    = $this->getTemplateData($types);
        $globals = $this->twigEnvironment->getGlobals();
        if (!isset($globals['app'])) {
            return [];
        }

        $app = $globals['app'];
        if ($app instanceof AppVariable && 'dev' == $app->getDebug()) {
            return $data;
        }

        return [];
    }
}
