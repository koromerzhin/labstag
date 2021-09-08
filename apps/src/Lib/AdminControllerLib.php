<?php

namespace Labstag\Lib;

use Labstag\Entity\Attachment;
use Labstag\Entity\User;
use Labstag\Reader\UploadAnnotationReader;
use Labstag\Repository\AttachmentRepository;
use Labstag\RequestHandler\AttachmentRequestHandler;
use Labstag\Singleton\AdminBtnSingleton;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class AdminControllerLib extends ControllerLib
{

    protected AttachmentRequestHandler $attachmentRH;

    protected ?AdminBtnSingleton $btns = null;

    protected FlashBagInterface $flashbag;

    protected string $headerTitle = '';

    protected RouterInterface $router;

    protected TokenStorageInterface $token;

    protected string $urlHome = '';

    public function addBreadcrumbs(array $breadcrumbs): void
    {
        $this->breadcrumbsInstance->add($breadcrumbs);
    }

    public function create(
        UploadAnnotationReader $uploadAnnotReader,
        AttachmentRepository $attachmentRepository,
        AttachmentRequestHandler $attachmentRH,
        RequestHandlerLib $handler,
        object $entity,
        string $formType,
        array $url = [],
        string $twig = 'admin/crud/form.html.twig'
    ): Response
    {
        $routeCurrent = $this->get('request_stack')->getCurrentRequest()->get('_route');
        $breadcrumb   = [
            'New' => $this->generateUrl(
                $routeCurrent
            ),
        ];
        $this->addBreadcrumbs($breadcrumb);
        if (isset($url['list'])) {
            $this->btnInstance()->addBtnList(
                $url['list']
            );
        }

        $oldEntity = clone $entity;
        $form      = $this->createForm($formType, $entity);
        $this->btnInstance()->addBtnSave($form->getName(), 'Ajouter');
        $form->handleRequest($this->get('request_stack')->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $this->upload($uploadAnnotReader, $attachmentRepository, $attachmentRH, $entity);
            $handler->handle($oldEntity, $entity);
            if (isset($url['list'])) {
                return new RedirectResponse(
                    $this->generateUrl($url['list'])
                );
            }
        }

        return $this->render(
            $twig,
            [
                'entity' => $entity,
                'form'   => $form->createView(),
            ]
        );
    }

    public function listOrTrash(
        ServiceEntityRepositoryLib $repository,
        array $methods,
        string $html,
        array $url = [],
        array $actions = []
    ): Response
    {
        $routeCurrent = $this->get('request_stack')->getCurrentRequest()->get('_route');
        $routeType    = (0 != substr_count($routeCurrent, 'trash')) ? 'trash' : 'all';
        $method       = $methods[$routeType];

        if ('trash' == $routeType) {
            $this->listOrTrashRouteTrash($url, $actions, $repository);
        } elseif (isset($url['trash'])) {
            $this->setTrashIcon($methods, $repository, $url, $actions);
        }

        if (isset($url['new']) && 'trash' != $routeType) {
            $this->btnInstance()->addBtnNew(
                $url['new']
            );
        }

        if ('trash' != $routeType) {
            $this->btnInstance()->addSupprimerSelection(
                [
                    'redirect' => [
                        'href'   => $this->get('request_stack')->getCurrentRequest()->get('_route'),
                        'params' => [],
                    ],
                    'url'      => [
                        'href'   => 'api_action_deleties',
                        'params' => [
                            'entity' => strtolower(
                                str_replace(
                                    'Labstag\\Entity\\',
                                    '',
                                    $repository->getClassName()
                                )
                            ),
                        ],
                    ],
                ],
                'deleties'
            );
        }

        $pagination = $this->paginator->paginate(
            $repository->{$method}(),
            $this->get('request_stack')->getCurrentRequest()->query->getInt('page', 1),
            10
        );

        if ('trash' == $routeType && 0 == $pagination->count()) {
            throw new AccessDeniedException();
        }

        return $this->render(
            $html,
            [
                'pagination' => $pagination,
                'actions'    => $actions,
            ]
        );
    }

    public function modalAttachmentDelete(): void
    {
        $twig                      = $this->get('twig');
        $globals                   = $twig->getGlobals();
        $modal                     = $globals['modal'] ?? [];
        $modal['attachmentdelete'] = true;
        $twig->addGlobal('modal', $modal);
    }

    public function render(
        string $view,
        array $parameters = [],
        ?Response $response = null
    ): Response
    {
        $this->setBreadcrumbsPage();
        $parameters = array_merge(
            $parameters,
            [
                'btnadmin' => $this->btnInstance()->get(),
            ]
        );

        return parent::render($view, $parameters, $response);
    }

    public function renderShowOrPreview(
        object $entity,
        string $twigShow,
        array $url = []
    ): Response
    {
        $routeCurrent = $this->get('request_stack')->getCurrentRequest()->get('_route');
        $routeType    = (0 != substr_count($routeCurrent, 'preview')) ? 'preview' : 'show';
        $this->showOrPreviewaddBreadcrumbs($url, $routeType, $routeCurrent, $entity);
        $this->showOrPreviewaddBtnList($url, $routeType);
        $this->showOrPreviewaddBtnGuard($url, $routeType, $entity);
        $this->showOrPreviewaddBtnTrash($url, $routeType);
        $this->showOrPreviewaddBtnEdit($url, $routeType, $entity);
        $this->showOrPreviewaddBtnRestore($url, $routeType, $entity);
        $this->showOrPreviewaddBtnDestroy($url, $routeType, $entity);

        if (isset($url['delete']) && 'show' == $routeType) {
            $urlsDelete = [
                'delete' => $url['delete'],
            ];
            if (isset($url['list'])) {
                $urlsDelete['list'] = $url['list'];
            }

            $this->btnInstance()->addBtnDelete(
                $entity,
                $urlsDelete,
                'Supprimer',
                [
                    'id'     => $entity->getId(),
                    'entity' => $this->classEntity($entity),
                ]
            );
        }

        if ('preview' == $routeType && is_null($entity->getDeletedAt())) {
            throw new AccessDeniedException();
        }

        return $this->render(
            $twigShow,
            ['entity' => $entity]
        );
    }

    public function update(
        UploadAnnotationReader $uploadAnnotReader,
        AttachmentRepository $attachmentRepository,
        AttachmentRequestHandler $attachmentRH,
        RequestHandlerLib $handler,
        string $formType,
        object $entity,
        array $url = [],
        string $twig = 'admin/crud/form.html.twig'
    ): Response
    {
        $this->denyAccessUnlessGranted('edit', $entity);
        $routeCurrent = $this->get('request_stack')->getCurrentRequest()->get('_route');
        $breadcrumb   = [
            'edit' => $this->generateUrl(
                $routeCurrent,
                [
                    'id' => $entity->getId(),
                ]
            ),
        ];
        $this->addBreadcrumbs($breadcrumb);
        $this->setBtnViewUpdate($url, $entity);
        $oldEntity = clone $entity;
        $form      = $this->createForm($formType, $entity);
        $this->btnInstance()->addBtnSave($form->getName(), 'Sauvegarder');
        $form->handleRequest($this->get('request_stack')->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $this->upload($uploadAnnotReader, $attachmentRepository, $attachmentRH, $entity);
            $handler->handle($oldEntity, $entity);
            $this->flashBagAdd(
                'success',
                $this->translator->trans('data.save')
            );
            if (isset($url['list'])) {
                return new RedirectResponse(
                    $this->generateUrl($url['list'])
                );
            }
        }

        return $this->render(
            $twig,
            [
                'entity' => $entity,
                'form'   => $form->createView(),
            ]
        );
    }

    protected function btnInstance()
    {
        if (is_null($this->btns)) {
            $this->btns = AdminBtnSingleton::getInstance();
        }

        if (!$this->btns->isInit()) {
            $this->btns->setConf(
                $this->get('twig'),
                $this->get('router'),
                $this->get('security.token_storage'),
                $this->get('security.csrf.token_manager'),
                $this->guardService
            );
        }

        return $this->btns;
    }

    protected function classEntity($entity)
    {
        $class = get_class($entity);

        $class = str_replace('Labstag\\Entity\\', '', $class);

        return strtolower($class);
    }

    protected function enableBtnGuard($entity): bool
    {
        if ($entity instanceof User) {
            $routes = $this->guardService->getGuardRoutesForUser($entity);

            return (0 != count($routes)) ? true : false;
        }

        $routes = $this->guardService->getGuardRoutesForGroupe($entity);

        return (0 != count($routes)) ? true : false;
    }

    protected function isRouteEnable(
        string $route
    )
    {
        $token = $this->get('security.token_storage');

        return $this->guardService->guardRoute($route, $token->getToken());
    }

    protected function listOrTrashRouteTrash(
        array $url,
        array $actions,
        ServiceEntityRepositoryLib $repository
    )
    {
        $entity = strtolower(
            str_replace(
                'Labstag\\Entity\\',
                '',
                $repository->getClassName()
            )
        );

        $breadcrumb = [
            'Trash' => $this->generateUrl(
                'admin_adresseuser_trash'
            ),
        ];
        $this->addBreadcrumbs($breadcrumb);
        if (isset($url['list'])) {
            $this->btnInstance()->addBtnList(
                $url['list']
            );
        }

        if (isset($url['empty'])) {
            $this->btnInstance()->addBtnEmpty(
                [
                    'empty' => $url['empty'],
                    'list'  => $url['list'],
                ],
                $entity
            );
        }

        $twig             = $this->get('twig');
        $globals          = $twig->getGlobals();
        $modal            = $globals['modal'] ?? [];
        $modal['destroy'] = (isset($actions['destroy']));
        $modal['restore'] = (isset($actions['restore']));
        $twig->addGlobal('modal', $modal);

        $this->btnInstance()->addViderSelection(
            [
                'redirect' => [
                    'href'   => $this->get('request_stack')->getCurrentRequest()->get('_route'),
                    'params' => [],
                ],
                'url'      => [
                    'href'   => 'api_action_destroies',
                    'params' => [
                        'entity' => strtolower(
                            str_replace(
                                'Labstag\\Entity\\',
                                '',
                                $repository->getClassName()
                            )
                        ),
                    ],
                ],
            ],
            'destroies'
        );

        $this->btnInstance()->addRestoreSelection(
            [
                'redirect' => [
                    'href'   => $this->get('request_stack')->getCurrentRequest()->get('_route'),
                    'params' => [],
                ],
                'url'      => [
                    'href'   => 'api_action_restories',
                    'params' => [
                        'entity' => strtolower(
                            str_replace(
                                'Labstag\\Entity\\',
                                '',
                                $repository->getClassName()
                            )
                        ),
                    ],
                ],
            ],
            'restories'
        );
    }

    protected function setAttachment(
        AttachmentRepository $attachmentRepository,
        $accessor,
        $entity,
        $annotation
    ): Attachment
    {
        $attachmentField = $accessor->getValue($entity, $annotation->getFilename());
        if (is_null($attachmentField)) {
            return new Attachment();
        }

        $attachment = $attachmentRepository->findOneBy(['id' => $attachmentField->getId()]);
        if (!$attachment instanceof Attachment) {
            $attachment = new Attachment();
        }

        return $attachment;
    }

    protected function setBreadcrumbsPage()
    {
        if ('' == $this->headerTitle && '' == $this->urlHome) {
            return;
        }

        $router      = $this->get('router');
        $breadcrumbs = [
            $this->headerTitle => $router->generate(
                $this->urlHome
            ),
        ];

        $this->breadcrumbsInstance->addPosition($breadcrumbs, 0);
    }

    protected function setBtnDelete(array $url, object $entity): void
    {
        if (!isset($url['delete'])) {
            return;
        }

        $urlsDelete = [
            'delete' => $url['delete'],
        ];
        if (isset($url['list'])) {
            $urlsDelete['list'] = $url['list'];
        }

        $this->btnInstance()->addBtnDelete(
            $entity,
            $urlsDelete,
            'Supprimer',
            [
                'id'     => $entity->getId(),
                'entity' => $this->classEntity($entity),
            ]
        );
    }

    protected function setBtnGuard(array $url, object $entity): void
    {
        if (!isset($url['guard']) || !$this->enableBtnGuard($entity)) {
            return;
        }

        $this->btnInstance()->addBtnGuard(
            $url['guard'],
            'Guard',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    protected function setBtnList(array $url): void
    {
        if (!isset($url['list'])) {
            return;
        }

        $this->btnInstance()->addBtnList(
            $url['list'],
            'Liste',
        );
    }

    protected function setBtnShow(array $url, object $entity): void
    {
        if (!isset($url['show'])) {
            return;
        }

        $this->btnInstance()->addBtnShow(
            $url['show'],
            'Show',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    protected function setBtnViewUpdate(
        array $url,
        object $entity
    ): void
    {
        $this->setBtnList($url);
        $this->setBtnShow($url, $entity);
        $this->setBtnGuard($url, $entity);
        $this->setBtnDelete($url, $entity);
    }

    protected function setTrashIcon(
        $methods,
        $repository,
        $url,
        $actions
    )
    {
        $entityManager = $this->getDoctrine()->getManager();
        $methodTrash   = $methods['trash'];
        $entityManager->getFilters()->disable('softdeleteable');
        $total = $repository->{$methodTrash}();
        $entityManager->getFilters()->enable('softdeleteable');
        if (0 != count($total)) {
            $this->btnInstance()->addBtnTrash(
                $url['trash']
            );
        }

        $twig              = $this->get('twig');
        $globals           = $twig->getGlobals();
        $modal             = $globals['modal'] ?? [];
        $modal['delete']   = (isset($actions['delete']));
        $modal['workflow'] = (isset($actions['workflow']));

        $twig->addGlobal('modal', $modal);
    }

    protected function showOrPreviewaddBreadcrumbs($url, $routeType, $routeCurrent, $entity)
    {
        if ('preview' == $routeType && isset($url['trash'])) {
            $breadcrumb = [
                'Trash' => $this->generateUrl(
                    $url['trash']
                ),
            ];
            $this->addBreadcrumbs($breadcrumb);
        }

        $breadcrumb = [
            $routeType => $this->generateUrl(
                $routeCurrent,
                [
                    'id' => $entity->getId(),
                ]
            ),
        ];
        $this->addBreadcrumbs($breadcrumb);
    }

    protected function showOrPreviewaddBtnDestroy($url, $routeType, $entity)
    {
        if (!(isset($url['destroy']) && 'preview' == $routeType)) {
            return;
        }

        $this->btnInstance()->addBtnDestroy(
            $entity,
            [
                'destroy' => $url['destroy'],
                'list'    => $url['trash'],
            ],
            'Destroy',
            [
                'id'     => $entity->getId(),
                'entity' => $this->classEntity($entity),
            ]
        );
    }

    protected function showOrPreviewaddBtnEdit($url, $routeType, $entity)
    {
        if (!(isset($url['edit']) && 'show' == $routeType) || !$this->isGranted('edit', $entity)) {
            return;
        }

        $this->btnInstance()->addBtnEdit(
            $url['edit'],
            'Editer',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    protected function showOrPreviewaddBtnGuard(
        $url,
        $routeType,
        $entity
    )
    {
        if (!(isset($url['guard']) && 'show' == $routeType) || !$this->enableBtnGuard($entity)) {
            return;
        }

        $this->btnInstance()->addBtnGuard(
            $url['guard'],
            'Guard',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    protected function showOrPreviewaddBtnList($url, $routeType)
    {
        if (!(isset($url['list']) && 'show' == $routeType)) {
            return;
        }

        $this->btnInstance()->addBtnList(
            $url['list'],
            'Liste',
        );
    }

    protected function showOrPreviewaddBtnRestore($url, $routeType, $entity)
    {
        if (isset($url['restore']) && 'preview' == $routeType) {
            $this->btnInstance()->addBtnRestore(
                $entity,
                [
                    'restore' => $url['restore'],
                    'list'    => $url['trash'],
                ],
                'Restore',
                [
                    'id'     => $entity->getId(),
                    'entity' => $this->classEntity($entity),
                ]
            );
        }
    }

    protected function showOrPreviewaddBtnTrash($url, $routeType)
    {
        if (!(isset($url['trash']) && 'preview' == $routeType)) {
            return;
        }

        $this->btnInstance()->addBtnTrash(
            $url['trash'],
            'Trash',
        );
    }

    protected function upload(
        UploadAnnotationReader $uploadAnnotReader,
        AttachmentRepository $attachmentRepository,
        AttachmentRequestHandler $attachmentRH,
        $entity
    )
    {
        if (!$uploadAnnotReader->isUploadable($entity)) {
            return;
        }

        $annotations = $uploadAnnotReader->getUploadableFields($entity);
        foreach ($annotations as $property => $annotation) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $file     = $accessor->getValue($entity, $property);
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $attachment = $this->setAttachment(
                $attachmentRepository,
                $accessor,
                $entity,
                $annotation
            );
            $old        = clone $attachment;

            $filename = $file->getClientOriginalName();
            $path     = $this->getParameter('file_directory').'/'.$annotation->getPath();
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $file->move(
                $path,
                $filename
            );
            $file = $path.'/'.$filename;
            $attachment->setMimeType(mime_content_type($file));
            $attachment->setSize(filesize($file));
            $size = getimagesize($file);
            $attachment->setDimensions(is_array($size) ? $size : []);
            $attachment->setName(
                str_replace(
                    $this->getParameter('kernel.project_dir').'/public/',
                    '',
                    $file
                )
            );
            $attachmentRH->handle($old, $attachment);
            $accessor->setValue($entity, $annotation->getFilename(), $attachment);
        }
    }
}
