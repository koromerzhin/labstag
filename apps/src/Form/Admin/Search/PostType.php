<?php

namespace Labstag\Form\Admin\Search;

use Labstag\Entity\Post;
use Labstag\Lib\SearchAbstractTypeLib;
use Labstag\Search\PostSearch;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends SearchAbstractTypeLib
{
    /**
     * @inheritDoc
     */
    public function buildForm(
        FormBuilderInterface $formBuilder,
        array $options
    ): void
    {
        $formBuilder->add(
            'title',
            TextType::class,
            [
                'required' => false,
                'label'    => $this->translator->trans('post.title.label', [], 'admin.search.form'),
                'help'     => $this->translator->trans('post.title.help', [], 'admin.search.form'),
                'attr'     => [
                    'placeholder' => $this->translator->trans('post.title.placeholder', [], 'admin.search.form'),
                ],
            ]
        );
        $this->addRefUser($formBuilder);
        $this->addRefCategory($formBuilder);
        $this->addPublished($formBuilder);
        $this->showState(
            $formBuilder,
            new Post(),
            $this->translator->trans('post.etape.label', [], 'admin.search.form'),
            $this->translator->trans('post.etape.help', [], 'admin.search.form'),
            $this->translator->trans('post.etape.placeholder', [], 'admin.search.form')
        );
        parent::buildForm($formBuilder, $options);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class'      => PostSearch::class,
                'csrf_protection' => false,
                'method'          => 'GET',
            ]
        );
    }
}
