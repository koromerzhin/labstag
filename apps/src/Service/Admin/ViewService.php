<?php

namespace Labstag\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Menu;
use Labstag\Entity\Paragraph;
use Labstag\Interfaces\DomainInterface;
use Labstag\Interfaces\EntityInterface;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Lib\RepositoryLib;
use Labstag\Repository\ParagraphRepository;
use Labstag\Service\AttachFormService;
use Labstag\Service\BlockService;
use Labstag\Service\BreadcrumbService;
use Labstag\Service\DataService;
use Labstag\Service\DomainService;
use Labstag\Service\ErrorService;
use Labstag\Service\FileService;
use Labstag\Service\GuardService;
use Labstag\Service\MenuService;
use Labstag\Service\OauthService;
use Labstag\Service\RepositoryService;
use Labstag\Service\SessionService;
use Labstag\Service\TrashService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\TraceableUrlMatcher;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use Twig\Environment;
use UnitEnum;

class ViewService
{
    /**
     * @var int
     */
    final public const STATUSRESPONSE = 200;

    protected ?DomainInterface $domain = null;

    public function __construct(
        protected OauthService $oauthService,
        protected TokenStorageInterface $tokenStorage,
        protected CsrfTokenManagerInterface $csrfTokenManager,
        protected TrashService $trashService,
        protected ErrorService $errorService,
        protected CacheInterface $cache,
        protected DataService $dataService,
        protected FileService $fileService,
        protected ParameterBagInterface $parameterBag,
        protected BlockService $blockService,
        protected BreadcrumbService $breadcrumbService,
        protected RouterInterface $router,
        protected MenuService $menuService,
        protected SessionService $sessionService,
        protected TranslatorInterface $translator,
        protected GuardService $guardService,
        protected AttachFormService $attachFormService,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected FormFactoryInterface $formFactory,
        protected PaginatorInterface $paginator,
        protected Environment $twigEnvironment,
        protected EntityManagerInterface $entityManager,
        protected BtnService $btnService,
        protected RepositoryService $repositoryService,
        protected RequestStack $requeststack,
        protected DomainService $domainService
    )
    {
    }

    public function edit(
        EntityInterface $entity,
        array $parameters = []
    ): Response
    {
        return $this->editOrNew('edit', $entity, $parameters);
    }

    public function getDomain(): DomainInterface
    {
        $domain = $this->domain;
        if (!$domain instanceof DomainInterface) {
            throw new Exception('Domain not found');
        }

        return $domain;
    }

    public function index(
        array $parameters = []
    ): Response
    {
        return $this->listOrTrash('index', $parameters);
    }

    public function modalAttachmentDelete(): void
    {
        $globals = $this->twigEnvironment->getGlobals();
        $modal   = $globals['modal'] ?? [];
        if (!is_array($modal)) {
            $modal = [];
        }

        $modal['attachmentdelete'] = true;
        $this->twigEnvironment->addGlobal('modal', $modal);
    }

    public function new(
        array $parameters = []
    ): Response
    {
        $domain = $this->getDomain();
        if (!$domain instanceof DomainInterface) {
            throw new Exception('Domain not found');
        }

        $class  = $domain->getEntity();
        $entity = new $class();
        if (!$entity instanceof EntityInterface) {
            throw new Exception('Entity not found');
        }

        return $this->editOrNew('new', $entity, $parameters);
    }

    public function preview(
        EntityInterface $entity
    ): Response
    {
        return $this->showOrPreview($entity, 'preview');
    }

    public function render(
        string $view,
        array $parameters = [],
        ?Response $response = null
    ): Response
    {
        $parameters = $this->generateMenus($parameters);
        $this->setBreadcrumbsPage();
        $parameters = $this->setTitleHeader($parameters);

        $parameters['btnadmin'] = $this->btnService->get();

        $content = $this->renderView($view, $parameters);

        $response ??= new Response();

        if (self::STATUSRESPONSE === $response->getStatusCode()) {
            foreach ($parameters as $parameter) {
                if ($parameter instanceof FormInterface && $parameter->isSubmitted() && !$parameter->isValid()) {
                    $response->setStatusCode(422);

                    break;
                }
            }
        }

        $response->setContent($content);

        return $response;
    }

    public function setDomain(string $entity): void
    {
        $domainLib = $this->domainService->getDomain($entity);
        if (!$domainLib instanceof DomainInterface) {
            throw new Exception('Domain not found');
        }

        $this->domain = $domainLib;
    }

    public function show(
        EntityInterface $entity
    ): Response
    {
        return $this->showOrPreview($entity, 'show');
    }

    public function trash(
        array $parameters = []
    ): Response
    {
        return $this->listOrTrash('trash', $parameters);
    }

    protected function addNewBreadcrumb(
        Route $data,
        array $routeParam,
        string $route
    ): void
    {
        $compiledRoute   = $data->compile();
        $breadcrumbTitle = [
            ...$this->setHeaderTitle(),
            ...$this->domainService->getTitles(),
        ];
        $title = '';
        foreach ($breadcrumbTitle as $key => $value) {
            if ($key == $route) {
                $title = $value;

                break;
            }
        }

        if ('' == $title) {
            return;
        }

        $variables = $compiledRoute->getPathVariables();
        $params    = [];
        foreach ($variables as $variable) {
            if (isset($routeParam[$variable])) {
                $params[$variable] = $routeParam[$variable];
            }
        }

        if ((is_countable($variables) ? count($variables) : 0) !== count($params)) {
            return;
        }

        $this->breadcrumbService->add(
            $title,
            $this->generateUrl(
                $route,
                $params,
            )
        );
    }

    protected function createForm(
        string $type,
        mixed $data = null,
        array $options = []
    ): FormInterface
    {
        return $this->formFactory->create($type, $data, $options);
    }

    protected function createNotFoundException(
        string $message = 'Not Found',
        ?Throwable $throwable = null
    ): NotFoundHttpException
    {
        return new NotFoundHttpException($message, $throwable);
    }

    protected function denyAccessUnlessGranted(
        string $attribute,
        EntityInterface $entity,
        string $message = 'Access Denied.'
    ): void
    {
        if (!$this->isGranted($attribute, $entity)) {
            $accessDeniedException = new AccessDeniedException($message, null);
            $accessDeniedException->setAttributes([$attribute]);
            $accessDeniedException->setSubject($entity);

            throw $accessDeniedException;
        }
    }

    protected function editOrNew(
        string $type,
        EntityInterface $entity,
        array $parameters = []
    ): Response
    {
        $domain = $this->getDomain();
        if (!$domain instanceof DomainInterface) {
            throw new Exception('Domain not found');
        }

        $url = $domain->getUrlAdmin();
        [
            $template,
            $form,
        ] = $this->initEditOrNew($domain, $type, $entity, $url);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->setPositionParagraphs();
            $this->attachFormService->upload($entity);
            $repositoryLib = $domain->getRepository();
            $repositoryLib->save($entity);
            $this->sessionService->flashBagAdd(
                'success',
                $this->translator->trans('data.save')
            );
            if (isset($url['list'])) {
                return $this->redirectToRoute($url['list']);
            }
        }

        $parameters = [
            ...$parameters,
            'entity' => $entity,
            'form'   => $form,
        ];

        return $this->render(
            $template,
            $parameters
        );
    }

    protected function generateUrl(
        string $route,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }

    /**
     * Gets a container parameter by its name.
     */
    protected function getParameter(string $name): array|bool|string|int|float|UnitEnum|null
    {
        return $this->parameterBag->get($name);
    }

    protected function isGranted(
        string $method,
        EntityInterface $entity
    ): bool
    {
        return $this->authorizationChecker->isGranted($method, $entity);
    }

    protected function isRouteEnable(
        string $route
    ): bool
    {
        return $this->guardService->guardRoute(
            $route,
            $this->tokenStorage->getToken()
        );
    }

    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }

    protected function renderView(string $view, array $parameters = []): string
    {
        foreach ($parameters as $k => $v) {
            if ($v instanceof FormInterface) {
                $parameters[$k] = $v->createView();
            }
        }

        return $this->twigEnvironment->render($view, $parameters);
    }

    protected function setBreadcrumbsPage(): void
    {
        $routeCollection     = $this->router->getRouteCollection();
        $requestContext      = $this->router->getContext();
        $traceableUrlMatcher = new TraceableUrlMatcher($routeCollection, $requestContext);
        /** @var Request $request */
        $request    = $this->requeststack->getCurrentRequest();
        $attributes = $request->attributes->all();
        $pathinfo   = $request->getPathInfo();
        $breadcrumb = $this->getBreadcrumb($traceableUrlMatcher, $pathinfo, []);
        $breadcrumb = array_reverse($breadcrumb);

        $all         = $routeCollection->all();
        $routeParams = $attributes['_route_params'];
        foreach ($breadcrumb as $row) {
            $name  = $row['name'];
            $route = $all[$name];
            $this->addNewBreadcrumb($route, $routeParams, $name);
        }

        $data = $this->breadcrumbService->get();
        $this->twigEnvironment->addGlobal('breadcrumbs', $data);
    }

    protected function setHeaderTitle(): array
    {
        return [
            'admin'        => $this->translator->trans('admin.title', [], 'admin.header'),
            'admin_oauth'  => $this->translator->trans('oauth.title', [], 'admin.header'),
            'admin_param'  => $this->translator->trans('param.title', [], 'admin.header'),
            'admin_profil' => $this->translator->trans('profil.title', [], 'admin.header'),
            'admin_trash'  => $this->translator->trans('trash.title', [], 'admin.header'),
        ];
    }

    protected function setPagination(
        string $routeType
    ): PaginationInterface
    {
        $domain = $this->getDomain();
        if (!$domain instanceof DomainInterface) {
            throw new Exception('Domain not found');
        }

        $serviceEntityRepositoryLib = $domain->getRepository();
        $methods                    = $domain->getMethodsList();
        $method                     = $methods[$routeType];
        /** @var Request $request */
        $request = $this->requeststack->getCurrentRequest();
        $query   = $request->query;
        $get     = $query->all();
        $limit   = $query->getInt('limit', 10);
        /** @var callable $callable */
        $callable = [
            $serviceEntityRepositoryLib,
            $method,
        ];

        return $this->paginator->paginate(
            call_user_func($callable, $get),
            $query->getInt('page', 1),
            $limit
        );
    }

    protected function setPositionEntity(Request $request, string $entityclass): void
    {
        $position = $request->request->get('position');
        if (!empty($position)) {
            $position = json_decode((string) $position, true, 512, JSON_THROW_ON_ERROR);
        }

        if (is_iterable($position)) {
            foreach ($position as $row) {
                $id       = $row['id'];
                $position = (int) $row['position'];
                /** @var RepositoryLib $repository */
                $repository = $this->repositoryService->get($entityclass);
                /** @var Block|Chapter|Menu $entity */
                $entity = $repository->find($id);
                if (!is_null($entity)) {
                    $entity->setPosition($position + 1);
                    $repository->save($entity);
                }
            }
        }
    }

    protected function setSearchForms(
        array $parameters
    ): array
    {
        $domain = $this->getDomain();
        if (!$domain instanceof DomainInterface) {
            throw new Exception('Domain not found');
        }

        /** @var Request $request */
        $request = $this->requeststack->getCurrentRequest();
        $query   = $request->query;
        $get     = $query->all();
        $form    = $domain->getSearchForm();
        if ('' == $form) {
            return $parameters;
        }

        $searchLib = $domain->getSearchData();
        $searchLib->search($get, $this->repositoryService);

        $route = $request->get('_route');
        if (!is_string($route)) {
            return $parameters;
        }

        $url        = $this->router->generate($route);
        $searchForm = $this->createForm(
            $form,
            $searchLib,
            [
                'attr'   => ['id' => 'searchform'],
                'action' => $url,
            ]
        );

        $parameters['searchform'] = $searchForm;

        return $parameters;
    }

    private function addInBreadcrumb(array $breadcrumb, array $trace): array
    {
        $add = true;
        foreach ($breadcrumb as $row) {
            if ($row['name'] == $trace['name']) {
                $add = false;

                break;
            }
        }

        if ($add) {
            $breadcrumb[] = $trace;
        }

        return $breadcrumb;
    }

    private function generateMenus(array $parameters = []): array
    {
        return [
            ...$parameters,
            'allmenu' => $this->menuService->createMenus(),
        ];
    }

    private function getBreadcrumb(
        TraceableUrlMatcher $traceableUrlMatcher,
        string $pathinfo,
        array $breadcrumb
    ): array
    {
        $traces = $traceableUrlMatcher->getTraces($pathinfo);
        foreach ($traces as $trace) {
            $testadmin = 0 != substr_count((string) $trace['name'], 'admin');
            if (TraceableUrlMatcher::ROUTE_MATCHES == $trace['level'] && $testadmin) {
                $breadcrumb = $this->addInBreadcrumb($breadcrumb, $trace);
            }
        }

        if (0 != substr_count((string) $pathinfo, '/')) {
            $newpathinfo = substr((string) $pathinfo, 0, strrpos((string) $pathinfo, '/') + 1);
            if ($newpathinfo === $pathinfo) {
                $newpathinfo = substr((string) $pathinfo, 0, (int) strrpos((string) $pathinfo, '/'));
            }

            $breadcrumb = $this->getBreadcrumb(
                $traceableUrlMatcher,
                $newpathinfo,
                $breadcrumb
            );
        }

        return $breadcrumb;
    }

    private function initEditOrNew(
        DomainInterface $domain,
        string $type,
        EntityInterface $entity,
        array $url
    ): array
    {
        $templates = $domain->getTemplates();
        $template  = (array_key_exists($type, $templates)) ? $templates[$type] : 'admin/crud/form.html.twig';

        $this->modalAttachmentDelete();
        $formType = $domain->getType();
        $this->denyAccessUnlessGranted(
            null === $entity->getId() || '' === $entity->getId() ? 'new' : 'edit',
            $entity
        );
        $this->btnService->setBtnViewUpdate($url, $entity);
        $form = $this->createForm($formType, $entity);
        $this->btnService->addBtnSave(
            $form->getName(),
            null === $entity->getId() || '' === $entity->getId() ? 'Ajouter' : 'Sauvegarder'
        );
        if ($form->has('paragraph')) {
            $this->modalParagraphs();
        }

        $form->handleRequest($this->requeststack->getCurrentRequest());

        return [
            $template,
            $form,
        ];
    }

    private function listOrTrash(
        string $type,
        array $parameters = []
    ): Response
    {
        $domain = $this->getDomain();
        if (!$domain instanceof DomainInterface) {
            throw new Exception('Domain not found');
        }

        $templates = $domain->getTemplates();
        if (!array_key_exists($type, $templates)) {
            throw new Exception('Template not found');
        }

        $url = $domain->getUrlAdmin();
        /** @var Request $request */
        $request   = $this->requeststack->getCurrentRequest();
        $all       = $request->attributes->all();
        $route     = $all['_route'];
        $routeType = (0 != substr_count((string) $route, 'trash')) ? 'trash' : 'all';
        $this->btnService->setBtnListOrTrash($domain, $routeType);
        $pagination = $this->setPagination($routeType);

        if ('trash' == $routeType && 0 == $pagination->count()) {
            throw new AccessDeniedException();
        }

        $parameters = [
            ...$parameters,
            'pagination' => $pagination,
            'actions'    => $url,
        ];
        $parameters = $this->setSearchForms($parameters);

        $template = $templates[$type];

        return $this->render(
            $template,
            $parameters
        );
    }

    private function modalParagraphs(): void
    {
        $globals = $this->twigEnvironment->getGlobals();
        $modal   = $globals['modal'] ?? [];
        if (!is_array($modal)) {
            $modal = [];
        }

        $modal['paragraphs'] = true;
        $this->twigEnvironment->addGlobal('modal', $modal);
    }

    private function setPositionParagraphs(): void
    {
        /** @var Request $request */
        $request    = $this->requeststack->getCurrentRequest();
        $paragraphs = $request->request->all('paragraphs');
        if (!is_iterable($paragraphs)) {
            return;
        }

        /** @var ParagraphRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Paragraph::class);
        foreach ($paragraphs as $id => $position) {
            /** @var int $position */
            $paragraph = $repositoryLib->find($id);
            if (!$paragraph instanceof Paragraph) {
                continue;
            }

            $paragraph->setPosition($position);
            $repositoryLib->save($paragraph);
        }
    }

    private function setTitleHeader(array $parameters): array
    {
        /** @var Request $request */
        $request = $this->requeststack->getCurrentRequest();
        $all     = $request->attributes->all();
        $route   = $all['_route'];
        $headers = $this->domainService->getTitles();
        $header  = '';
        foreach ($headers as $key => $title) {
            if ($key == $route) {
                $header = $title;

                break;
            }

            if (0 != substr_count((string) $route, (string) $key)) {
                $header = $title;
            }
        }

        if (!empty($header)) {
            $parameters['headerTitle'] = $header;
        }

        return $parameters;
    }

    private function showOrPreview(
        EntityInterface $entity,
        string $type
    ): Response
    {
        $domain = $this->getDomain();
        if (!$domain instanceof DomainInterface) {
            throw new Exception('Domain not found');
        }

        $templates = $domain->getTemplates();
        if (!array_key_exists($type, $templates)) {
            throw new Exception('Template not found');
        }

        $template = $templates[$type];

        /** @var EntityTrashInterface $entity */
        $url = $domain->getUrlAdmin();
        /** @var Request $request */
        $request      = $this->requeststack->getCurrentRequest();
        $routeCurrent = $request->get('_route');
        if (!is_string($routeCurrent)) {
            throw new AccessDeniedException();
        }

        $routeType = (0 != substr_count((string) $routeCurrent, 'preview')) ? 'preview' : 'show';
        $this->btnService->showOrPreviewadd($url, $routeType, $entity);

        if (isset($url['delete']) && 'show' == $routeType) {
            $this->btnService->setBtnDelete($url, $entity);
        }

        if ('preview' == $routeType && is_null($entity->getDeletedAt())) {
            throw new AccessDeniedException();
        }

        $parameters = ['entity' => $entity];

        return $this->render(
            $template,
            $parameters
        );
    }
}
