<?php

namespace Labstag\Lib;

use Labstag\Entity\Paragraph;
use Labstag\Interfaces\EntityBlockInterface;
use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Service\FrontService;
use Labstag\Service\MenuService;
use Labstag\Service\ParagraphService;
use Labstag\Service\RepositoryService;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class BlockLib extends AbstractController
{
    public function __construct(
        protected CacheInterface $cache,
        protected RepositoryService $repositoryService,
        protected MenuService $menuService,
        protected ParagraphService $paragraphService,
        protected FrontService $frontService,
        protected RequestStack $requestStack,
        protected RouterInterface $router,
        protected TranslatorInterface $translator,
        protected Environment $twigEnvironment,
        protected array $template = []
    )
    {
    }

    public function getClassCSS(
        array $dataClass,
        EntityBlockInterface $entityBlock
    ): array
    {
        $block       = $entityBlock->getBlock();
        $dataClass[] = $block->getRegion().'-block-'.$block->getType();

        return $dataClass;
    }

    public function getCode(EntityBlockInterface $entityBlock, ?EntityFrontInterface $entityFront): string
    {
        unset($entityBlock, $entityFront);

        return '';
    }

    public function template(
        EntityBlockInterface $entityBlock,
        ?EntityFrontInterface $entityFront
    ): array
    {
        return $this->showTemplateFile($this->getCode($entityBlock, $entityFront));
    }

    public function twig(EntityBlockInterface $entityBlock, ?EntityFrontInterface $entityFront): string
    {
        return $this->getTemplateFile($this->getCode($entityBlock, $entityFront));
    }

    public function view(string $twig, array $parameters = []): ?Response
    {
        return $this->render(
            $twig,
            $parameters
        );
    }

    protected function getParagraphsArray(
        ParagraphService $paragraphService,
        EntityFrontInterface $entityFront,
        array $paragraphs
    ): array
    {
        $methods = get_class_methods($entityFront);
        if (!in_array('getParagraphs', $methods)) {
            return $paragraphs;
        }

        $paragraphsArray = $entityFront->getParagraphs();
        foreach ($paragraphsArray as $paragraphArray) {
            /** @var Paragraph $paragraphArray */
            $context = $paragraphService->getContext($paragraphArray);
            if (is_null($context)) {
                continue;
            }

            $template = $paragraphService->showTemplate($paragraphArray);

            $paragraphs[] = [
                'class'    => $paragraphService->getClass($paragraphArray),
                'execute'  => 'view',
                'args'     => [
                    'twig'       => $paragraphService->getTwigTemplate($paragraphArray),
                    'parameters' => $context,
                ],
                'template' => $template,
            ];
        }

        return $paragraphs;
    }

    protected function getTemplateData(string $type): array
    {
        if (isset($this->template[$type])) {
            return $this->template[$type];
        }

        $loader   = $this->twigEnvironment->getLoader();
        $htmltwig = '.html.twig';

        $files = [
            'block/'.$type.$htmltwig,
            'block/default'.$htmltwig,
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
            'hook'  => 'block',
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

    protected function launchParagraphs(array $paragraphs): array
    {
        foreach ($paragraphs as $position => $row) {
            if ($row['args']['parameters'] instanceof RedirectResponse) {
                continue;
            }

            $callable = [
                $row['class'],
                $row['execute'],
            ];

            $content = call_user_func_array($callable, $row['args']);

            $paragraphs[$position]['data'] = $content;
        }

        return $paragraphs;
    }

    protected function setRedirect(array $paragraphs): mixed
    {
        $redirect = null;
        foreach ($paragraphs as $paragraph) {
            if (!$paragraph['args']['parameters'] instanceof RedirectResponse) {
                continue;
            }

            $redirect = $paragraph['args']['parameters'];

            break;
        }

        return $redirect;
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
