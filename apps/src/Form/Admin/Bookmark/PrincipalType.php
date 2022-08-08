<?php

namespace Labstag\Form\Admin\Bookmark;

use Labstag\Entity\Bookmark;
use Labstag\Entity\Category;
use Labstag\Entity\Libelle;
use Labstag\Entity\User;
use Labstag\FormType\SearchableType;
use Labstag\Lib\AbstractTypeLib;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrincipalType extends AbstractTypeLib
{
    /**
     * @inheritDoc
     */
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void
    {
        $this->setTextType($builder);
        $this->addPublished($builder);
        $builder->add(
            'url',
            UrlType::class,
            [
                'label'    => $this->translator->trans('bookmark.url.label', [], 'admin.form'),
                'help'     => $this->translator->trans('bookmark.url.help', [], 'admin.form'),
                'required' => false,
                'attr'     => [
                    'placeholder' => $this->translator->trans('bookmark.url.placeholder', [], 'admin.form'),
                ],
            ]
        );
        $this->setContent($builder);
        $builder->add(
            'file',
            FileType::class,
            [
                'label'    => $this->translator->trans('bookmark.file.label', [], 'admin.form'),
                'help'     => $this->translator->trans('bookmark.file.help', [], 'admin.form'),
                'required' => false,
                'attr'     => ['accept' => 'image/*'],
            ]
        );
        $builder->add(
            'refuser',
            SearchableType::class,
            [
                'label'    => $this->translator->trans('bookmark.refuser.label', [], 'admin.form'),
                'help'     => $this->translator->trans('bookmark.refuser.help', [], 'admin.form'),
                'multiple' => false,
                'class'    => User::class,
                'route'    => 'api_search_user',
                'attr'     => [
                    'placeholder' => $this->translator->trans('bookmark.refuser.placeholder', [], 'admin.form'),
                ],
            ]
        );
        $builder->add(
            'refcategory',
            SearchableType::class,
            [
                'label'    => $this->translator->trans('bookmark.refcategory.label', [], 'admin.form'),
                'help'     => $this->translator->trans('bookmark.refcategory.help', [], 'admin.form'),
                'multiple' => false,
                'class'    => Category::class,
                'route'    => 'api_search_category',
                'attr'     => [
                    'placeholder' => $this->translator->trans(
                        'bookmark.refcategory.placeholder',
                        [],
                        'admin.form'
                    ),
                ],
            ]
        );
        $builder->add(
            'libelles',
            SearchableType::class,
            [
                'label' => $this->translator->trans('bookmark.libelles.label', [], 'admin.form'),
                'help'  => $this->translator->trans('bookmark.libelles.help', [], 'admin.form'),
                'class' => Libelle::class,
                'new'   => new Libelle(),
                'add'   => true,
                'route' => 'api_search_postlibelle',
                'attr'  => [
                    'placeholder' => $this->translator->trans('bookmark.libelles.placeholder', [], 'admin.form'),
                ],
            ]
        );
        $this->setMeta($builder);
        unset($options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Bookmark::class,
            ]
        );
    }

    protected function setTextType($builder)
    {
        $texttype = [
            'name' => [
                'label' => $this->translator->trans('bookmark.name.label', [], 'admin.form'),
                'help'  => $this->translator->trans('bookmark.name.help', [], 'admin.form'),
                'attr'  => [
                    'placeholder' => $this->translator->trans('bookmark.name.placeholder', [], 'admin.form'),
                ],
            ],
            'slug' => [
                'label'    => $this->translator->trans('bookmark.slug.label', [], 'admin.form'),
                'help'     => $this->translator->trans('bookmark.slug.help', [], 'admin.form'),
                'attr'     => [
                    'placeholder' => $this->translator->trans('bookmark.slug.placeholder', [], 'admin.form'),
                ],
                'required' => false,
            ],
        ];
        foreach ($texttype as $key => $args) {
            $builder->add($key, TextType::class, $args);
        }
    }
}
