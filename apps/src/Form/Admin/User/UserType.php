<?php

namespace Labstag\Form\Admin\User;

use Labstag\Entity\Groupe;
use Labstag\Entity\User;
use Labstag\Form\Admin\Collections\User\AddressType;
use Labstag\Form\Admin\Collections\User\EmailType;
use Labstag\Form\Admin\Collections\User\LinkType;
use Labstag\Form\Admin\Collections\User\PhoneType;
use Labstag\FormType\SearchableType;
use Labstag\Lib\AbstractTypeLib;
use Labstag\Repository\EmailUserRepository;
use Labstag\Service\TemplatePageService;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AbstractTypeLib
{
    public function __construct(
        TranslatorInterface $translator,
        protected EmailUserRepository $repository,
        TemplatePageService $templatePageService
    )
    {
        parent::__construct($translator, $templatePageService);
    }

    /**
     * @inheritDoc
     */
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void
    {
        $builder->add(
            'username',
            TextType::class,
            [
                'label' => $this->translator->trans('user.username.label', [], 'admin.form'),
                'help'  => $this->translator->trans('user.username.help', [], 'admin.form'),
                'attr'  => [
                    'placeholder' => $this->translator->trans('user.username.placeholder', [], 'admin.form'),
                ],
            ]
        );
        $this->addPlainPassword($builder);
        $builder->add(
            'refgroupe',
            SearchableType::class,
            [
                'label'    => $this->translator->trans('user.refgroupe.label', [], 'admin.form'),
                'help'     => $this->translator->trans('user.refgroupe.help', [], 'admin.form'),
                'multiple' => false,
                'class'    => Groupe::class,
                'route'    => 'api_search_group',
                'attr'     => [
                    'placeholder' => $this->translator->trans('user.refgroupe.placeholder', [], 'admin.form'),
                ],
            ]
        );
        $builder->add(
            'file',
            FileType::class,
            [
                'label'    => ' ',
                'help'     => $this->translator->trans('user.file.help', [], 'admin.form'),
                'required' => false,
                'attr'     => ['accept' => 'image/*'],
            ]
        );
        $this->addEmails($builder, $options, $this->repository);

        $this->setCollectionTypeAll($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }

    protected function setCollectionTypeAll(FormBuilderInterface $builder)
    {
        $tab = [
            'emailUsers'   => EmailType::class,
            'phoneUsers'   => PhoneType::class,
            'addressUsers' => AddressType::class,
            'linkUsers'    => LinkType::class,
        ];
        $this->setCollectionType($builder, $tab);
    }
}
