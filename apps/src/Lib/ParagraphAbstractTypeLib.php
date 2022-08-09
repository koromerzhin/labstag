<?php

namespace Labstag\Lib;

use Labstag\Service\FormService;
use Labstag\Service\ParagraphService;
use Labstag\Service\TemplatePageService;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class ParagraphAbstractTypeLib extends AbstractTypeLib
{
    public function __construct(
        TranslatorInterface $translator,
        TemplatePageService $templatePageService,
        protected ParagraphService $paragraphService,
        Environment $twig,
        FormService $formService,
        ContainerBagInterface $containerBag
    )
    {
        $this->twig = $twig;
        parent::__construct($translator, $templatePageService);
    }

    public function getFieldEntity()
    {
        return '';
    }

    public function getForm()
    {
        $forms = $this->formService->all();
        $data  = [];
        foreach ($forms as $form) {
            $name        = $form->getName();
            $data[$name] = $name;
        }

        ksort($data);

        return $data;
    }

    public function getFormType()
    {
        return '';
    }

    protected function getRender($view, $param = [])
    {
        return $this->twig->render($view, $param);
    }
}