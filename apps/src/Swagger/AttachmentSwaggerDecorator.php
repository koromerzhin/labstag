<?php

namespace Labstag\Swagger;

use Labstag\Controller\Api\AttachmentDecorator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Adds the Swagger documentation for the AttachmentDecorator.
 *
 * @see AttachmentDecorator
 */
final class AttachmentSwaggerDecorator implements NormalizerInterface
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
        $docs = $this->decorated->normalize($object, $format, $context);
        $this->setProfilAvatar($docs);
        $this->setUserAvatar($docs);
        $this->setPostImg($docs);
        $this->setNoteInterneFond($docs);
        $this->setEditoFond($docs);

        return $docs;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    private function setEditoFond(&$docs)
    {
        $statsEndpoint = [
            'summary'    => 'edito fond.',
            'tags'       => ['Attachment'],
            'parameters' => [
                [
                    'name'        => 'entity',
                    'in'          => 'query',
                    'required'    => true,
                    'description' => 'entity',
                    'schema'      => ['type' => 'string'],
                ],
                [
                    'name'        => '_token',
                    'in'          => 'query',
                    'required'    => true,
                    'description' => 'token',
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

        $docs['paths']['/api/attachment/edito/fond/{entity}']['delete'] = $statsEndpoint;
    }

    private function setNoteInterneFond(&$docs)
    {
        $statsEndpoint = [
            'summary'    => 'node interne Fond.',
            'tags'       => ['Attachment'],
            'parameters' => [
                [
                    'name'        => 'entity',
                    'in'          => 'query',
                    'required'    => true,
                    'description' => 'entity',
                    'schema'      => ['type' => 'string'],
                ],
                [
                    'name'        => '_token',
                    'in'          => 'query',
                    'required'    => true,
                    'description' => 'token',
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

        $docs['paths']['/api/attachment/noteinterne/fond/{entity}']['delete'] = $statsEndpoint;
    }

    private function setPostImg(&$docs)
    {
        $statsEndpoint = [
            'summary'    => 'Post Img.',
            'tags'       => ['Attachment'],
            'parameters' => [
                [
                    'name'        => 'entity',
                    'in'          => 'query',
                    'required'    => true,
                    'description' => 'entity',
                    'schema'      => ['type' => 'string'],
                ],
                [
                    'name'        => '_token',
                    'in'          => 'query',
                    'required'    => true,
                    'description' => 'token',
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

        $docs['paths']['/api/attachment/post/img/{entity}']['delete'] = $statsEndpoint;
    }

    private function setProfilAvatar(&$docs)
    {
        $statsEndpoint = [
            'summary'    => 'Profil avatar.',
            'tags'       => ['Attachment'],
            'parameters' => [
                [
                    'name'        => '_token',
                    'in'          => 'query',
                    'required'    => true,
                    'description' => 'token',
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

        $docs['paths']['/api/attachment/profil/avatar']['delete'] = $statsEndpoint;
    }

    private function setUserAvatar(&$docs)
    {
        $statsEndpoint = [
            'summary'    => 'User avatar.',
            'tags'       => ['Attachment'],
            'parameters' => [
                [
                    'name'        => 'entity',
                    'in'          => 'query',
                    'required'    => true,
                    'description' => 'entity',
                    'schema'      => ['type' => 'string'],
                ],
                [
                    'name'        => '_token',
                    'in'          => 'query',
                    'required'    => true,
                    'description' => 'token',
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

        $docs['paths']['/api/attachment/user/avatar/{entity}']['delete'] = $statsEndpoint;
    }
}
