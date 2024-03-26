<?php

namespace Labstag\Service;

use Labstag\Entity\HttpErrorLogs;
use Labstag\Repository\HttpErrorLogsRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class HttpErrorService
{

    public function __construct(
        protected RequestStack $requestStack,
        protected HttpErrorLogsRepository $httpErrorLogsRepository
    )
    {
    }

    public function set($httpCode = 404)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (is_null($request)) {
            return;
        }

        $server        = $request->server;
        $httpErrorLogs = new HttpErrorLogs();
        $domain        = $server->get('REQUEST_SCHEME').'://'.$server->get('SERVER_NAME');
        $url           = $server->get('REQUEST_URI');
        $referer       = $request->headers->get('referer');
        $method        = $server->get('REQUEST_METHOD');
        $data          = $this->httpErrorLogsRepository->findBy(
            [
                'domain'         => $domain,
                'url'            => $url,
                'referer'        => $referer,
                'http_code'      => $httpCode,
                'request_method' => $method,
            ]
        );

        if (0 != count($data)) {
            return;
        }

        $httpErrorLogs->setDomain($domain);
        $httpErrorLogs->setUrl($url);
        $httpErrorLogs->setAgent($server->get('HTTP_USER_AGENT'));
        $httpErrorLogs->setHttpCode($httpCode);
        $httpErrorLogs->setIp($server->get('REMOTE_ADDR'));
        if (!is_null($referer)) {
            $httpErrorLogs->setReferer($referer);
        }

        $httpErrorLogs->setRequestData(
            [
                'get'  => $request->query->all(),
                'post' => $request->request->all(),
            ]
        );
        $httpErrorLogs->setRequestMethod($method);

        $this->httpErrorLogsRepository->save($httpErrorLogs, true);
    }
}
