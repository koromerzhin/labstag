<?php

namespace Labstag\Event\Subscriber;

use Labstag\Lib\EventSubscriberLib;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use tidy;

class KernelSubscriber extends EventSubscriberLib
{
    /**
     * @var string
     */
    final public const API_CONTROLLER = '/(Api)/';

    /**
     * @var string
     */
    final public const BLOCK_CONTROLLER = '/(Labstag\/Block)/';

    /**
     * @var int
     */
    final public const CLIENTNUMBER = 400;

    /**
     * @var string[]
     */
    final public const ERROR_CONTROLLER = [
        'error_controller',
        'error_controller::preview',
    ];

    /**
     * @var string
     */
    final public const LABSTAG_CONTROLLER = '/(Labstag)/';

    /**
     * @var string
     */
    final public const PARAGRAPH_CONTROLLER = '/(Labstag\/Paragraph)/';

    /**
     * @var string[]
     */
    final public const TAGS = [
        'attachment-delete',
        'attachment-img',
        'btn-addcollection',
        'btn-delete',
        'btn-togglefieldset',
        'confirm-close',
        'confirm-delete',
        'confirm-deleteattachment',
        'confirm-deleties',
        'confirm-destroy',
        'confirm-empties',
        'confirm-empty',
        'confirm-emptyall',
        'confirm-restore',
        'confirm-restories',
        'confirm-workflow',
        'guard-allroute',
        'guard-allworkflow',
        'guard-changeroute',
        'guard-changeworkflow',
        'guard-refgrouproute',
        'guard-refgroupworkflow',
        'guard-route',
        'guard-setroute',
        'guard-setworkflow',
        'guard-workflow',
        'input-city',
        'input-codepostal',
        'input-email',
        'input-gps',
        'input-phone',
        'input-url',
        'link-add',
        'link-btnadmin',
        'link-btnadmindelete',
        'link-btnadmindeleties',
        'link-btnadmindestroy',
        'link-btnadminempties',
        'link-btnadminempty',
        'link-btnadminemptyall',
        'link-btnadminmove',
        'link-btnadminnewblock',
        'link-btnadminrestore',
        'link-btnadminrestories',
        'link-delete',
        'link-destroy',
        'link-edit',
        'link-empty',
        'link-guard',
        'link-move',
        'link-restore',
        'link-show',
        'link-trash',
        'select-all',
        'select-country',
        'select-element',
        'select-selector',
        'table-datatable',
        'workflow-action',
    ];

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
            KernelEvents::RESPONSE => 'onKernelResponse'
        ];
    }

    public function onKernelException(ExceptionEvent $exceptionEvent): void
    {
        $throwable = $exceptionEvent->getThrowable();
        if (!$throwable instanceof NotFoundHttpException) {
            return;
        }

        $statusCode = (string) $throwable->getStatusCode();

        $this->httpErrorService->set($statusCode);
    }

    public function onKernelResponse(ResponseEvent $responseEvent): void
    {
        $response   = $responseEvent->getResponse();
        $request    = $responseEvent->getRequest();
        $controller = $request->attributes->get('_controller');
        if (!is_string($controller)) {
            return;
        }

        preg_match(self::LABSTAG_CONTROLLER, $controller, $matches);
        preg_match(self::API_CONTROLLER, $controller, $apis);
        preg_match(self::PARAGRAPH_CONTROLLER, $controller, $paragraphs);
        preg_match(self::BLOCK_CONTROLLER, $controller, $blocks);
        $count = count($apis) + count($paragraphs) + count($blocks);
        $test1 = (0 == count($matches) || in_array($controller, self::ERROR_CONTROLLER) || 0 != $count);
        $test2 = ('html' != $request->getRequestFormat() || $response->getStatusCode() >= self::CLIENTNUMBER);
        if ($test1 || $test2) {
            return;
        }

        $content = $response->getContent();
        $content = preg_replace('#<script>#i', '<script type="text/javascript">', (string) $content);

        // config https://api.html-tidy.org/tidy/quickref_5.6.0.html
        $config = [
            'indent'                      => true,
            'output-xhtml'                => true,
            'drop-empty-elements'         => false,
            'drop-proprietary-attributes' => false,
            'new-inline-tags'             => implode(' ', self::TAGS),
            'wrap'                        => 200,
        ];
        $tidy = new tidy();
        $tidy->parseString((string) $content, $config, 'utf8');
        $tidy->cleanRepair();

        $response->setContent($tidy->html()->value);
        $responseEvent->setResponse($response);
    }
}
