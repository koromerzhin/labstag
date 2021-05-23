<?php

namespace Labstag\Form\Admin\Post;

use Labstag\Entity\Post;
use Labstag\Entity\User;
use Labstag\FormType\SelectRefUserType;
use Labstag\FormType\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder->add('title');
        $builder->add(
            'slug',
            TextType::class,
            ['required' => false]
        );
        $builder->add('content', WysiwygType::class);
        $builder->add(
            'file',
            FileType::class,
            [
                'required' => false,
                'attr'     => ['accept' => 'image/*'],
            ]
        );
        $builder->add(
            'created',
            DateTimeType::class,
            [
                'date_widget'  => 'single_text',
                'time_widget'  => 'single_text',
                'with_seconds' => true,
            ]
        );
        $builder->add(
            'updated',
            DateTimeType::class,
            [
                'date_widget'  => 'single_text',
                'time_widget'  => 'single_text',
                'with_seconds' => true,
            ]
        );
        $builder->add(
            'refuser',
            SelectRefUserType::class,
            [
                'class' => User::class,
            ]
        );
        $builder->add('commentaire');
        $builder->add('libelles');
        unset($options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Post::class,
            ]
        );
    }
}
