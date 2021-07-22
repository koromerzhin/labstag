<?php

namespace Labstag\Controller\Api;

use Labstag\Entity\Attachment;
use Labstag\Entity\Edito;
use Labstag\Entity\NoteInterne;
use Labstag\Entity\Post;
use Labstag\Entity\User;
use Labstag\Lib\ApiControllerLib;
use Labstag\RequestHandler\EditoRequestHandler;
use Labstag\RequestHandler\NoteInterneRequestHandler;
use Labstag\RequestHandler\PostRequestHandler;
use Labstag\RequestHandler\UserRequestHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * @Route("/api/attachment")
 */
class AttachmentController extends ApiControllerLib
{
    /**
     * @Route("/edito/fond/{entity}", name="api_attachment_editofond")
     *
     * @return Response
     */
    public function editoFond(Edito $entity, EditoRequestHandler $editoRH): JsonResponse
    {
        return $this->deleteFile($entity, $editoRH, 'getFond', 'setFond');
    }

    /**
     * @Route("/noteinterne/fond/{entity}", name="api_attachment_noteinternefond")
     *
     * @return Response
     */
    public function noteinterneFond(NoteInterne $entity, NoteInterneRequestHandler $noteInterneRH): JsonResponse
    {
        return $this->deleteFile($entity, $noteInterneRH, 'getFond', 'setFond');
    }

    /**
     * @Route("/post/img/{entity}", name="api_attachment_postimg")
     *
     * @return Response
     */
    public function postImg(Post $entity, PostRequestHandler $postRequestHandler): JsonResponse
    {
        return $this->deleteFile($entity, $postRequestHandler, 'getImg', 'setImg');
    }

    /**
     * @Route("/profil/avatar", name="api_attachment_profilavatar")
     *
     * @return Response
     */
    public function profilAvatar(UserRequestHandler $userRequestHandler): JsonResponse
    {
        $return = [
            'state' => false,
            'error' => '',
        ];
        $token  = $this->token->getToken();
        $entity = $token->getUser();
        $token  = $this->verifToken($entity);
        if (!$token) {
            $return['error'] = 'Token incorrect';

            return new JsonResponse($return);
        }

        $old        = clone $entity;
        $attachment = $entity->getAvatar();
        $this->deleteAttachment($attachment);
        $entity->setAvatar(null);
        $userRequestHandler->handle($old, $entity);
        $return['state'] = true;

        return new JsonResponse($return);
    }

    /**
     * @Route("/user/avatar/{entity}", name="api_attachment_useravatar")
     *
     * @return Response
     */
    public function userAvatar(User $entity, UserRequestHandler $userRequestHandler): JsonResponse
    {
        return $this->deleteFile($entity, $userRequestHandler, 'getAvatar', 'setAvatar');
    }

    protected function deleteAttachment(?Attachment $attachment)
    {
        if (is_null($attachment)) {
            return;
        }

        $this->entityManager->remove($attachment);
        $this->entityManager->flush();
    }

    protected function verifToken($entity): bool
    {
        $token = $this->request->request->get('_token');

        $csrfToken = new CsrfToken(
            'attachment-img-'.$entity->getId(),
            $token
        );

        return $this->csrfTokenManager->isTokenValid($csrfToken);
    }

    private function deleteFile($entity, $requesthandler, $methodGet, $methodSet)
    {
        $return = [
            'state' => false,
            'error' => '',
        ];
        $token  = $this->verifToken($entity);
        if (!$token) {
            $return['error'] = 'Token incorrect';

            return new JsonResponse($return);
        }

        $old        = clone $entity;
        $attachment = $entity->$methodGet();
        $this->deleteAttachment($attachment);
        $entity->$methodSet(null);
        $requesthandler->handle($old, $entity);
        $return['state'] = true;

        return new JsonResponse($return);
    }
}
