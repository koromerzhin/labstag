<?php

namespace Labstag\Form\Admin\Collections\Param;

use Labstag\FormType\CoreTextareaType;
use Labstag\Lib\AbstractTypeLib;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TarteaucitronType extends AbstractTypeLib
{
    public function buildForm(
        FormBuilderInterface $formBuilder,
        array $options
    ): void
    {
        $formBuilder->add(
            'privacyUrl',
            UrlType::class,
            [
                'label'    => $this->translator->trans('param.tarteaucitron.privacyUrl.label', [], 'admin.form'),
                'help'     => $this->translator->trans('param.tarteaucitron.privacyUrl.help', [], 'admin.form'),
                'required' => false,
                'attr'     => [
                    'placeholder' => $this->translator->trans(
                        'param.tarteaucitron.privacyUrl.placeholder',
                        [],
                        'admin.form'
                    ),
                ],
            ]
        );

        $this->setInputTextAll($formBuilder);
        $this->setInputTrueFalsePartie1($formBuilder);
        $formBuilder->add(
            'iconPosition',
            ChoiceType::class,
            [
                'label'   => $this->translator->trans('param.tarteaucitron.iconPosition.label', [], 'admin.form'),
                'help'    => $this->translator->trans('param.tarteaucitron.iconPosition.help', [], 'admin.form'),
                'attr'    => [
                    'placeholder' => $this->translator->trans(
                        'param.tarteaucitron.iconPosition.placeholder',
                        [],
                        'admin.form'
                    ),
                ],
                'choices' => [
                    'BottomRight' => 'BottomRight',
                    'BottomLeft'  => 'BottomLeft',
                    'TopRight'    => 'TopRight',
                    'TopLeft'     => 'TopLeft',
                ],
            ]
        );
        $this->setInputTrueFalsePartie2($formBuilder);
        $formBuilder->add(
            'readmoreLink',
            UrlType::class,
            [
                'label'    => $this->translator->trans('param.tarteaucitron.readmoreLink.label', [], 'admin.form'),
                'help'     => $this->translator->trans('param.tarteaucitron.readmoreLink.help', [], 'admin.form'),
                'required' => false,
                'attr'     => [
                    'placeholder' => $this->translator->trans(
                        'param.tarteaucitron.readmoreLink.placeholder',
                        [],
                        'admin.form'
                    ),
                ],
            ]
        );
        $formBuilder->add(
            'mandatory',
            ChoiceType::class,
            [
                'label'   => $this->translator->trans('param.tarteaucitron.mandatory.label', [], 'admin.form'),
                'help'    => $this->translator->trans('param.tarteaucitron.mandatory.help', [], 'admin.form'),
                'attr'    => [
                    'placeholder' => $this->translator->trans(
                        'param.tarteaucitron.mandatory.placeholder',
                        [],
                        'admin.form'
                    ),
                ],
                'choices' => [
                    'Non' => '0',
                    'Oui' => '1',
                ],
            ]
        );
        $formBuilder->add(
            'job',
            CoreTextareaType::class,
            [
                'label' => $this->translator->trans('param.tarteaucitron.job.label', [], 'admin.form'),
                'help'  => $this->translator->trans('param.tarteaucitron.job.help', [], 'admin.form'),
            ]
        );
        unset($options);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        // Configure your form options here
        $optionsResolver->setDefaults(
            []
        );
    }

    private function setInputTextAll(FormBuilderInterface $formBuilder): void
    {
        $tab = [
            'hashtag'     => [
                'label'       => $this->translator->trans('param.tarteaucitron.hashtag.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.hashtag.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.hashtag.placeholder',
                    [],
                    'admin.form'
                ),
            ],
            'cookieName'  => [
                'label'       => $this->translator->trans('param.tarteaucitron.cookieName.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.cookieName.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.cookieName.placeholder',
                    [],
                    'admin.form'
                ),
            ],
            'orientation' => [
                'label'       => $this->translator->trans('param.tarteaucitron.orientation.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.orientation.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.orientation.placeholder',
                    [],
                    'admin.form'
                ),
            ],
        ];
        $this->setInputText($formBuilder, $tab);
    }

    private function setInputTrueFalse(
        FormBuilderInterface $formBuilder,
        array $tab
    ): void
    {
        foreach ($tab as $id => $row) {
            $formBuilder->add(
                $id,
                ChoiceType::class,
                [
                    'label'   => $row['label'],
                    'help'    => $row['help'],
                    'choices' => [
                        'Non' => '0',
                        'Oui' => '1',
                    ],
                    'attr'    => [
                        'placeholder' => $row['placeholder'],
                    ],
                ]
            );
        }
    }

    private function setInputTrueFalsePartie1(FormBuilderInterface $formBuilder): void
    {
        $tab = [
            'groupServices'  => [
                'label'       => $this->translator->trans('param.tarteaucitron.groupServices.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.groupServices.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.groupServices.placeholder',
                    [],
                    'admin.form'
                ),
            ],
            'showAlertSmall' => [
                'label'       => $this->translator->trans('param.tarteaucitron.showAlertSmall.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.showAlertSmall.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.showAlertSmall.placeholder',
                    [],
                    'admin.form'
                ),
            ],
            'cookieslist'    => [
                'label'       => $this->translator->trans('param.tarteaucitron.cookieslist.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.cookieslist.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.cookieslist.placeholder',
                    [],
                    'admin.form'
                ),
            ],
            'closePopup'     => [
                'label'       => $this->translator->trans('param.tarteaucitron.closePopup.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.closePopup.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.closePopup.placeholder',
                    [],
                    'admin.form'
                ),
            ],
            'showIcon'       => [
                'label'       => $this->translator->trans('param.tarteaucitron.showIcon.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.showIcon.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.showIcon.placeholder',
                    [],
                    'admin.form'
                ),
            ],
        ];
        $this->setInputTrueFalse($formBuilder, $tab);
    }

    private function setInputTrueFalsePartie2(FormBuilderInterface $formBuilder): void
    {
        $tab = [
            'adblocker'               => [
                'label'       => $this->translator->trans('param.tarteaucitron.adblocker.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.adblocker.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.adblocker.placeholder',
                    [],
                    'admin.form'
                ),
            ],
            'DenyAllCta'              => [
                'label'       => $this->translator->trans('param.tarteaucitron.DenyAllCta.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.DenyAllCta.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.DenyAllCta.placeholder',
                    [],
                    'admin.form'
                ),
            ],
            'AcceptAllCta'            => [
                'label'       => $this->translator->trans('param.tarteaucitron.AcceptAllCta.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.AcceptAllCta.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.AcceptAllCta.placeholder',
                    [],
                    'admin.form'
                ),
            ],
            'highPrivacy'             => [
                'label'       => $this->translator->trans('param.tarteaucitron.highPrivacy.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.highPrivacy.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.highPrivacy.placeholder',
                    [],
                    'admin.form'
                ),
            ],
            'handleBrowserDNTRequest' => [
                'label'       => $this->translator->trans(
                    'param.tarteaucitron.handleBrowserDNTRequest.label',
                    [],
                    'admin.form'
                ),
                'help'        => $this->translator->trans(
                    'param.tarteaucitron.handleBrowserDNTRequest.help',
                    [],
                    'admin.form'
                ),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.handleBrowserDNTRequest.placeholder',
                    [],
                    'admin.form'
                ),
            ],
            'removeCredit'            => [
                'label'       => $this->translator->trans('param.tarteaucitron.removeCredit.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.removeCredit.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.removeCredit.placeholder',
                    [],
                    'admin.form'
                ),
            ],
            'moreInfoLink'            => [
                'label'       => $this->translator->trans('param.tarteaucitron.moreInfoLink.label', [], 'admin.form'),
                'help'        => $this->translator->trans('param.tarteaucitron.moreInfoLink.help', [], 'admin.form'),
                'placeholder' => $this->translator->trans(
                    'param.tarteaucitron.moreInfoLink.placeholder',
                    [],
                    'admin.form'
                ),
            ],
        ];
        $this->setInputTrueFalse($formBuilder, $tab);
    }
}
