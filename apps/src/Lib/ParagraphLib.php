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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class ParagraphLib extends AbstractController
{

    protected array $template = [];

    public function __construct(
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

    public function getCode(EntityParagraphInterface $entityParagraph): string
    {
        unset($entityParagraph);

        return '';
    }

    public function setData(Paragraph $paragraph): void
    {
        unset($paragraph);
    }

    public function template(EntityParagraphInterface $entityParagraph): array
    {
        return $this->showTemplateFile($this->getCode($entityParagraph));
    }

    protected function getTemplateData(string $type): array
    {
        if (isset($this->template[$type])) {
            return $this->template[$type];
        }

        $loader   = $this->twigEnvironment->getLoader();
        $htmltwig = '.html.twig';
        $files    = [
            'paragraph/'.$type.$htmltwig,
            'paragraph/default'.$htmltwig,
        ];

        $view = end($files);

        foreach ($files as $file) {
            if (!$loader->exists($file)) {
                continue;
            }

            $view = $file;

            break;
        }

        $this->template[$type] = [
            'hook'  => 'paragraph',
            'type'  => $type,
            'files' => $files,
            'view'  => $view,
        ];

        return $this->template[$type];
    }

    protected function getTemplateFile(string $type): string
    {
        $data = $this->getTemplateData($type);

        return $data['view'];
    }

    protected function showTemplateFile(string $type): array
    {
        $data    = $this->getTemplateData($type);
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
