<?php

declare(strict_types=1);

namespace Labstag\OpenApi;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use Labstag\Lib\OpenApiLib;
use Symfony\Component\HttpFoundation\Response;

class SearchOpenApi extends OpenApiLib
{
    public function __invoke(array $context = []): OpenApi
    {
        $openApi   = $this->openApiFactory->__invoke($context);
        $functions = [
            'setUsers',
            'setLibelles',
            'setGroupes',
            'setCategories',
        ];

        foreach ($functions as $function) {
            /** @var callable $callable */
            $callable = [
                $this,
                $function,
            ];
            /** @var OpenApi $openApi */
            $openApi = call_user_func($callable, $openApi);
        }

        return $openApi;
    }

    protected function setCategories(OpenApi $openApi): OpenApi
    {
        $paths = $openApi->getPaths();
        $paths->addPath(
            '/api/search/category',
            new PathItem(
                description: 'Category',
                get: new Operation(
                    tags: ['Search'],
                    summary: 'Category',
                    responses: $this->getResponses(),
                    parameters: $this->setParameters(),
                ),
            )
        );

        return $openApi;
    }

    protected function setGroupes(OpenApi $openApi): OpenApi
    {
        $paths = $openApi->getPaths();
        $paths->addPath(
            '/api/search/group',
            new PathItem(
                description: 'Groupe',
                get: new Operation(
                    tags: ['Search'],
                    summary: 'Groupe',
                    responses: $this->getResponses(),
                    parameters: $this->setParameters(),
                ),
            )
        );

        return $openApi;
    }

    protected function setLibelles(OpenApi $openApi): OpenApi
    {
        $paths = $openApi->getPaths();
        $paths->addPath(
            '/api/search/libelle',
            new PathItem(
                description: 'Libelle',
                get: new Operation(
                    tags: ['Search'],
                    summary: 'Libelle',
                    responses: $this->getResponses(),
                    parameters: $this->setParameters(),
                ),
            ),
        );

        return $openApi;
    }

    protected function setParameters(): array
    {
        return [
            [
                'name'        => 'name',
                'in'          => 'query',
                'required'    => true,
                'description' => 'name',
                'schema'      => ['type' => 'string'],
            ],
        ];
    }

    protected function setUsers(OpenApi $openApi): OpenApi
    {
        $paths = $openApi->getPaths();
        $paths->getPath(
            '/api/search/user',
        );

        return $openApi;
    }

    private function getResponses(): array
    {
        return [
            Response::HTTP_OK => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type'       => 'object',
                            'properties' => [
                                'id'   => [
                                    'type'    => 'string',
                                    'example' => '56e96fa9-dc44-494d-885c-797c7d588449',
                                ],
                                'name' => [
                                    'type'    => 'string',
                                    'example' => 'name',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
