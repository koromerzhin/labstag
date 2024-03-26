<?php

namespace Labstag\Form\Admin;

use Labstag\Entity\Menu;
use Labstag\Lib\AbstractTypeLib;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractTypeLib
{
    public function buildForm(
        FormBuilderInterface $formBuilder,
        array $options
    ): void
    {
        unset($options);
        $entity = $formBuilder->getData();
        if (!$entity instanceof Menu) {
            return;
        }

        if (null === $entity->getClef() || '' === $entity->getClef()) {
            $this->setChildren($formBuilder);

            return;
        }

        $this->setNew($formBuilder);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class' => Menu::class,
            ]
        );
    }

    private function setChildren(FormBuilderInterface $formBuilder): void
    {
        $formBuilder->add(
            'name',
            TextType::class,
            [
                'label' => $this->translator->trans('menu.link.name.label', [], 'admin.form'),
                'help'  => $this->translator->trans('menu.link.name.help', [], 'admin.form'),
                'attr'  => [
                    'placeholder' => $this->translator->trans('menu.link.name.placeholder', [], 'admin.form'),
                ],
            ]
        );
        $formBuilder->add(
            'icon',
            TextType::class,
            [
                'label'    => $this->translator->trans('menu.link.icon.label', [], 'admin.form'),
                'help'     => $this->translator->trans('menu.link.icon.help', [], 'admin.form'),
                'attr'     => [
                    'placeholder' => $this->translator->trans('menu.link.icon.placeholder', [], 'admin.form'),
                ],
                'required' => false,
            ]
        );

        $formBuilder->add($this->setData($formBuilder));
    }

    private function setData(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $formBuilder = $formBuilder->create(
            'data',
            FormType::class,
            ['by_reference' => false]
        );
        $all     = $this->guardService->allRoutes();
        $choices = [];
        foreach (array_keys($all) as $key) {
            $choices[$key] = $key;
        }

        $formBuilder->add(
            'route',
            ChoiceType::class,
            [
                'choices'  => $choices,
                'label'    => $this->translator->trans('menu.data.route.name.label', [], 'admin.form'),
                'help'     => $this->translator->trans('menu.data.route.name.help', [], 'admin.form'),
                'required' => false,
                'attr'     => [
                    'placeholder' => $this->translator->trans('menu.data.route.name.placeholder', [], 'admin.form'),
                ],
            ]
        );
        $formBuilder->add(
            'param',
            TextType::class,
            [
                'label'    => $this->translator->trans('menu.data.route.param.label', [], 'admin.form'),
                'help'     => $this->translator->trans('menu.data.route.param.help', [], 'admin.form'),
                'required' => false,
                'attr'     => [
                    'placeholder' => $this->translator->trans('menu.data.route.param.placeholder', [], 'admin.form'),
                ],
            ]
        );
        $formBuilder->add(
            'url',
            TextType::class,
            [
                'label'    => $this->translator->trans('menu.data.route.url.label', [], 'admin.form'),
                'help'     => $this->translator->trans('menu.data.route.url.help', [], 'admin.form'),
                'required' => false,
                'attr'     => [
                    'placeholder' => $this->translator->trans('menu.data.route.url.placeholder', [], 'admin.form'),
                ],
            ]
        );
        $formBuilder->add(
            'target',
            ChoiceType::class,
            [
                'label'    => $this->translator->trans('menu.data.route.target.label', [], 'admin.form'),
                'help'     => $this->translator->trans('menu.data.route.target.help', [], 'admin.form'),
                'required' => false,
                'choices'  => [
                    ''        => '',
                    '_self'   => '_self',
                    '_blank'  => '_blank',
                    '_parent' => '_parent',
                    '_top'    => '_top',
                ],
                'attr'     => [
                    'placeholder' => $this->translator->trans('menu.data.route.target.placeholder', [], 'admin.form'),
                ],
            ]
        );

        return $formBuilder;
    }

    private function setNew(FormBuilderInterface $formBuilder): void
    {
        $formBuilder->add(
            'clef',
            TextType::class,
            [
                'label' => $this->translator->trans('menu.principal.clef.label', [], 'admin.form'),
                'help'  => $this->translator->trans('menu.principal.clef.help', [], 'admin.form'),
                'attr'  => [
                    'placeholder' => $this->translator->trans('menu.principal.clef.placeholder', [], 'admin.form'),
                ],
            ]
        );
    }
}
