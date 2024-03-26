<?php

namespace Labstag\Lib;

use Labstag\Service\BlockService;
use Labstag\Service\GuardService;
use Labstag\Service\ParagraphService;
use Labstag\Service\PhoneService;
use Labstag\Service\RepositoryService;
use Labstag\Service\WorkflowService;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;

abstract class ExtensionLib extends AbstractExtension
{

    protected array $templates = [];

    public function __construct(
        protected BlockService $blockService,
        protected RepositoryService $repositoryService,
        protected WorkflowService $workflowService,
        protected ParagraphService $paragraphService,
        protected PhoneService $phoneService,
        protected CacheManager $cacheManager,
        protected Environment $twigEnvironment,
        protected TokenStorageInterface $tokenStorage,
        protected GuardService $guardService,
    )
    {
    }

    protected function formPrototypeData(array $blockPrefixes, string $state): array
    {
        $type = ('collection_entry' != $blockPrefixes[1]) ? $blockPrefixes[2] : 'other';
        if (isset($this->templates['prototype'][$type])) {
            return $this->templates['prototype'][$type];
        }

        $files = [
            'prototype/'.$state.'/'.$type.'.html.twig',
            'prototype/'.$state.'/default.html.twig',
        ];
        $view = $this->getViewByFiles($files);

        $this->templates['prototype'][$type] = [
            'hook'  => 'prototype',
            'type'  => $type,
            'files' => $files,
            'view'  => $view,
        ];

        return $this->templates['prototype'][$type];
    }

    protected function getformClassData(mixed $class, string $state): array
    {
        $file = 'forms/'.$state.'/default.html.twig';

        $methods = get_class_vars($class::class);
        if (!array_key_exists('vars', $methods)
            || !array_key_exists('data', $class->vars)
            || is_null($class->vars['data'])
        ) {
            return [
                'hook'  => 'form',
                'type'  => 'other',
                'files' => [$file],
                'view'  => $file,
            ];
        }

        $vars = $class->vars;
        if (!is_array($vars)) {
            $vars = [];
        }

        $type = strtolower($this->setTypeformClass($vars));
        if (isset($this->templates['form'][$type])) {
            return $this->templates['form'][$type];
        }

        $files = $this->setFilesformClass($type, $class, $state);
        $view  = $this->getViewByFiles($files);

        $this->templates['form'][$type] = [
            'hook'  => 'form',
            'type'  => $type,
            'files' => $files,
            'view'  => $view,
        ];

        return $this->templates['form'][$type];
    }

    protected function getViewByFiles(array $files): string
    {
        $loader = $this->twigEnvironment->getLoader();
        $view   = end($files);
        foreach ($files as $file) {
            if (!$loader->exists($file)) {
                continue;
            }

            $view = $file;

            break;
        }

        return $view;
    }

    protected function setFilesformClass(
        string $type,
        mixed $class,
        string $state
    ): array
    {
        $htmltwig  = '.html.twig';
        $formstate = 'forms/'.$state.'/';
        $files     = [
            $formstate.$type.$htmltwig,
        ];

        if (isset($class->vars)) {
            /** @var array $vars */
            $vars = $class->vars;
            if (!is_array($vars)) {
                $vars = [];
            }

            $classtype = (isset($vars['value']) && is_object($vars['value'])) ? $vars['value']::class : null;
            if (!is_null($classtype) && 1 == substr_count($classtype, '\Paragraph')) {
                $files[] = $formstate.'paragraph/'.$type.$htmltwig;
                $files[] = $formstate.'paragraph/default'.$htmltwig;
            }

            if (!is_null($classtype) && 1 == substr_count($classtype, '\Block')) {
                $files[] = $formstate.'block/'.$type.$htmltwig;
                $files[] = $formstate.'block/default'.$htmltwig;
            }
        }

        $files[] = $formstate.'default'.$htmltwig;

        return $files;
    }

    protected function setTypeformClass(array $class): string
    {
        if (is_object($class['data'])) {
            $tabClass = explode('\\', $class['data']::class);

            return end($tabClass);
        }

        return $class['form']->vars['unique_block_prefix'];
    }
}
