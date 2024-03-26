<?php

namespace Labstag\Interfaces;

interface PostFormInterface
{
    public function context(array $params): mixed;

    public function getForm(): string;
}
