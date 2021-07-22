<?php

namespace Labstag\Queue\Message;

class ServiceMethodMessage
{

    protected string $method;

    protected array $params;

    protected string $serviceName;

    public function __construct(
        string $serviceName,
        string $method,
        array $params = []
    )
    {
        $this->serviceName = $serviceName;
        $this->method      = $method;
        $this->params      = $params;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }
}
