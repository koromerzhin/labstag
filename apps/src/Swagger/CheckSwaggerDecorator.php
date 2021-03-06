<?php

namespace Labstag\Swagger;

use Labstag\Controller\Api\CheckController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Adds the Swagger documentation for the CheckController.
 *
 * @see CheckController
 */
final class CheckSwaggerDecorator implements NormalizerInterface
{

    private NormalizerInterface $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, ?string $format = null, array $context = [])
    {
        $docs          = $this->decorated->normalize($object, $format, $context);
        $statsEndpoint = [
            'summary'    => 'Phone number.',
            'tags'       => ['Check'],
            'parameters' => [
                [
                    'name'        => 'country',
                    'in'          => 'query',
                    'required'    => true,
                    'description' => 'country code',
                    'schema'      => ['type' => 'string'],
                ],
                [
                    'name'        => 'phone',
                    'in'          => 'query',
                    'required'    => true,
                    'description' => 'phone',
                    'schema'      => ['type' => 'string'],
                ],
            ],
            'responses'  => [
                Response::HTTP_OK => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'isvalid' => [
                                        'type'    => 'boolean',
                                        'example' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $docs['paths']['/api/check/phone']['get'] = $statsEndpoint;

        return $docs;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}
