<?php

namespace Labstag\Service\Admin;

use Doctrine\Common\Collections\Collection;
use Exception;
use Labstag\Entity\Paragraph;
use Labstag\Form\Admin\ParagraphType;
use Labstag\Interfaces\EntityInterface;
use Labstag\Interfaces\EntityWithParagraphInterface;
use Labstag\Interfaces\PublicInterface;
use Labstag\Reader\UploadAnnotationReader;
use Labstag\Repository\ParagraphRepository;
use Labstag\Service\AttachFormService;
use Labstag\Service\ParagraphService as ServiceParagraphService;
use Labstag\Service\SessionService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class ParagraphService
{
    /**
     * @var int
     */
    final public const STATUSRESPONSE = 200;

    private ?array $urls = [];

    public function __construct(
        protected SessionService $sessionService,
        protected FormFactoryInterface $formFactory,
        protected AttachFormService $attachFormService,
        protected Environment $twigEnvironment,
        protected UploadAnnotationReader $uploadAnnotationReader,
        protected RouterInterface $router,
        protected RequestStack $requeststack,
        protected ParagraphRepository $paragraphRepository,
        protected ServiceParagraphService $serviceParagraphService,
    )
    {
    }

    public function add(EntityWithParagraphInterface $entityWithParagraph): RedirectResponse
    {
        $request = $this->getRequest();
        $data    = $request->get('data');
        if (!is_string($data)) {
            throw new Exception('data is not string');
        }

        $this->serviceParagraphService->add($entityWithParagraph, $data);
        if (!isset($this->urls['list'])) {
            throw new Exception('urls is not set');
        }

        return $this->redirectToRoute(
            (string) $this->urls['list'],
            [
                'id' => $entityWithParagraph->getId(),
            ]
        );
    }

    public function delete(Paragraph $paragraph): Response
    {
        $public = $this->serviceParagraphService->getParent($paragraph);
        if (!$public instanceof PublicInterface) {
            throw new Exception('entity is not public interface');
        }

        $this->paragraphRepository->remove($paragraph);
        $this->sessionService->flashBagAdd('success', 'Paragraph supprimée.');
        if (!isset($this->urls['edit'])) {
            throw new Exception('urls is not set');
        }

        return $this->redirectToRoute(
            (string) $this->urls['edit'],
            [
                'id'        => $public->getId(),
                '_fragment' => 'paragraph-list',
            ]
        );
    }

    public function list(Collection $paragraphs): Response
    {
        if (!isset($this->urls['edit']) || !isset($this->urls['delete'])) {
            throw new Exception('urls is not set');
        }

        return $this->render(
            'admin/paragraph/list.html.twig',
            [
                'paragraphs' => $paragraphs,
                'urledit'    => (string) $this->urls['edit'],
                'urldelete'  => (string) $this->urls['delete'],
            ]
        );
    }

    public function setUrls(
        string $urlList,
        string $urlEdit,
        string $urlShow,
        string $urlDelete
    ): void
    {
        $this->urls = [
            'list'   => $urlList,
            'edit'   => $urlEdit,
            'show'   => $urlShow,
            'delete' => $urlDelete,
        ];
    }

    public function show(
        Paragraph $paragraph
    ): Response
    {
        $form = $this->createForm(
            ParagraphType::class,
            $paragraph
        );
        $this->modalAttachmentDelete($paragraph, $form);
        /** @var Request $request */
        $request = $this->requeststack->getCurrentRequest();
        $form->handleRequest($request);
        /** @var EntityInterface $entityParagraph */
        $entityParagraph = $this->serviceParagraphService->getEntity($paragraph);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->paragraphRepository->save($paragraph);
            $this->attachFormService->upload($entityParagraph);
            $this->sessionService->flashBagAdd('success', 'Paragraph sauvegardé.');
            $referer = (string) $request->headers->get('referer');

            return new RedirectResponse($referer);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->sessionService->flashBagAdd('danger', 'Erreur lors de la modification.');
        }

        return $this->render(
            'admin/paragraph/show.html.twig',
            [
                'paragraph' => $paragraph,
                'form'      => $form,
            ]
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

    protected function generateUrl(
        string $route,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string
    {
        return $this->router->generate($route, $parameters, $referenceType);
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

    private function getRequest(): Request
    {
        $request = $this->requeststack->getCurrentRequest();
        if (!$request instanceof Request) {
            throw new Exception('Request not found');
        }

        return $request;
    }

    private function modalAttachmentDelete(Paragraph $paragraph, FormInterface $form): void
    {
        /** @var EntityInterface $entityParagraph */
        $entityParagraph = $this->serviceParagraphService->getEntity($paragraph);
        $annotations     = [
            ...$this->uploadAnnotationReader->getUploadableFields($paragraph),
            ...$this->uploadAnnotationReader->getUploadableFields($entityParagraph),
        ];
        if (0 == count($annotations)) {
            return;
        }

        $fields = $form->all();
        $enable = $this->uploadAnnotationReader->enableAttachment($annotations, $fields);
        if (!$enable) {
            return;
        }

        $globals = $this->twigEnvironment->getGlobals();
        $modal   = $globals['modal'] ?? [];
        if (!is_array($modal)) {
            $modal = [];
        }

        $modal['attachmentdelete'] = true;
        $this->twigEnvironment->mergeGlobals(['modal' => $modal]);
    }

    private function render(
        string $view,
        array $parameters = [],
        ?Response $response = null
    ): Response
    {
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
}
