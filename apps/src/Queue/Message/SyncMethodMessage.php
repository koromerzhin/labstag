<?php

namespace Labstag\Queue\Message;

class SyncMethodMessage
{
    public function __construct(protected string $serviceName, protected string $method, protected array $params = [])
    {
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
