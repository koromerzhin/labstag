<?php

namespace Labstag\Service;

use Labstag\Lib\AbstractTypeLib;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Response;

class FormService
{

    protected $frontform;

    protected $postform;

    protected $securityform;

    public function __construct(
        #[TaggedIterator('postform')]
        iterable $postform,
        #[TaggedIterator('securityform')]
        iterable $securityform,
        #[TaggedIterator('frontform')]
        iterable $frontform
    )
    {
        $this->postform     = $postform;
        $this->securityform = $securityform;
        $this->frontform    = $frontform;
    }

    public function context(
        object $formClass,
        array $params
    ): mixed
    {
        foreach ($this->postform as $row) {
            if ($row->getForm() != $formClass::class) {
                continue;
            }

            return $row->context($params);
        }

        return null;
    }

    public function getForm(): array
    {
        $data = [];
        foreach ($this->postform as $row) {
            $name        = $row->getName();
            $formclass   = $row->getForm();
            $form        = $this->getFormByClass($formclass);
            $data[$name] = $form->getBlockPrefix();
        }

        ksort($data);

        return $data;
    }

    public function init(string $nameform): ?object
    {
        $formClass = null;
        foreach ($this->postform as $row) {
            $formclass = $row->getForm();
            $form      = $this->getFormByClass($formclass);
            if ($form->getBlockPrefix() !== $nameform) {
                continue;
            }

            $formClass = $form;

            break;
        }

        return $formClass;
    }

    public function view(
        object $formClass,
        string $template,
        array $params
    ): ?Response
    {
        foreach ($this->postform as $row) {
            if ($row->getForm() != $formClass::class) {
                continue;
            }

            return $row->view($template, $params);
        }

        return null;
    }

    private function getFormByClass(string $class): ?AbstractTypeLib
    {
        $result = null;
        foreach ($this->getForms() as $form) {
            if ($form::class != $class) {
                continue;
            }

            $result = $form;

            break;
        }

        return $result;
    }

    private function getForms(): array
    {
        $data = [];
        foreach ($this->securityform as $form) {
            $data[] = $form;
        }

        foreach ($this->frontform as $form) {
            $data[] = $form;
        }

        return $data;
    }
}
