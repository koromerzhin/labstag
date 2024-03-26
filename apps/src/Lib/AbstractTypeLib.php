<?php

namespace Labstag\Lib;

use Labstag\Form\Admin\Collections\MetaType;
use Labstag\FormType\MinMaxCollectionType;
use Labstag\FormType\ParagraphType;
use Labstag\FormType\WysiwygType;
use Labstag\Service\GuardService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractTypeLib extends AbstractType
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected GuardService $guardService
    )
    {
    }

    protected function addParagraph(FormBuilderInterface $formBuilder, array $urls): void
    {
        $formBuilder->add(
            'paragraph',
            ParagraphType::class,
            [
                ...$urls,
                'mapped'   => false,
                'required' => false,
            ]
        );
    }

    protected function addPlainPassword(FormBuilderInterface $formBuilder): void
    {
        $formBuilder->add(
            'plainPassword',
            RepeatedType::class,
            [
                'type'            => PasswordType::class,
                'invalid_message' => $this->translator->trans('profil.password.match', [], 'admin.form'),
                'options'         => [
                    'attr' => ['class' => 'password-field'],
                ],
                'required'        => false,
                'first_options'   => [
                    'label' => $this->translator->trans('profil.password.label', [], 'admin.form'),
                    'help'  => $this->translator->trans('profil.password.help', [], 'admin.form'),
                ],
                'second_options'  => [
                    'label' => $this->translator->trans('profil.repeatpassword.label', [], 'admin.form'),
                    'help'  => $this->translator->trans('profil.repeatpassword.help', [], 'admin.form'),
                ],
            ]
        );
    }

    protected function addPublished(FormBuilderInterface $formBuilder): void
    {
        $formBuilder->add(
            'published',
            DateTimeType::class,
            [
                'label'        => $this->translator->trans('published.label', [], 'admin.form'),
                'help'         => $this->translator->trans('published.help', [], 'admin.form'),
                'date_widget'  => 'single_text',
                'time_widget'  => 'single_text',
                'with_seconds' => true,
            ]
        );
    }

    protected function setCollectionType(
        FormBuilderInterface $formBuilder,
        array $tab
    ): void
    {
        foreach ($tab as $key => $type) {
            $formBuilder->add(
                $key,
                MinMaxCollectionType::class,
                [
                    'label'        => ' ',
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'entry_type'   => $type,
                ]
            );
        }
    }

    protected function setContent(FormBuilderInterface $formBuilder): void
    {
        $formBuilder->add(
            'content',
            WysiwygType::class,
            [
                'label' => $this->translator->trans('content.label', [], 'admin.form'),
                'help'  => $this->translator->trans('content.help', [], 'admin.form'),
            ]
        );
    }

    protected function setInputText(
        FormBuilderInterface $formBuilder,
        array $tab
    ): void
    {
        foreach ($tab as $id => $row) {
            $formBuilder->add(
                $id,
                TextType::class,
                [
                    'label' => $row['label'],
                    'help'  => $row['help'],
                    'attr'  => [
                        'placeholder' => $row['placeholder'],
                    ],
                ]
            );
        }
    }

    protected function setMeta(FormBuilderInterface $formBuilder): void
    {
        $formBuilder->add(
            'metas',
            CollectionType::class,
            [
                'attr'         => ['data-limit' => 1],
                'label'        => 'Metatags',
                'entry_type'   => MetaType::class,
                'by_reference' => false,
                'allow_add'    => true,
                'allow_delete' => false,
            ]
        );
    }
}
