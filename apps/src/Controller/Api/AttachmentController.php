<?php

namespace Labstag\Controller\Api;

use Labstag\Entity\Attachment;
use Labstag\Entity\Bookmark;
use Labstag\Entity\Edito;
use Labstag\Entity\Memo;
use Labstag\Entity\Post;
use Labstag\Entity\User;
use Labstag\Lib\ApiControllerLib;
use Labstag\RequestHandler\EditoRequestHandler;
use Labstag\RequestHandler\MemoRequestHandler;
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
     * @Route("/bookmark/img/{entity}", name="api_attachment_bookmarkimg")
     */
    public function bookmarkImg(Bookmark $entity, PostRequestHandler $postRequestHandler): Response
    {
        return $this->deleteFile($entity, $postRequestHandler, 'getImg', 'setImg');
    }

    /**
     * @Route("/edito/fond/{entity}", name="api_attachment_editofond")
     */
    public function editoFond(Edito $entity, EditoRequestHandler $editoRH): Response
    {
        return $this->deleteFile($entity, $editoRH, 'getFond', 'setFond');
    }

    /**
     * @Route("/favicon", name="api_attachment_favicon")
     */
    public function favicon(): Response
    {
        return $this->setDataAttachment('getFavicon');
    }

    /**
     * @Route("/imagedefault", name="api_attachment_image")
     */
    public function imageDefault(): Response
    {
        return $this->setDataAttachment('getImageDefault');
    }

    /**
     * @Route("/memo/fond/{entity}", name="api_attachment_memofond")
     */
    public function memoFond(Memo $entity, MemoRequestHandler $noteInterneRH): Response
    {
        return $this->deleteFile($entity, $noteInterneRH, 'getFond', 'setFond');
    }

    /**
     * @Route("/post/img/{entity}", name="api_attachment_postimg")
     */
    public function postImg(Post $entity, PostRequestHandler $postRequestHandler): Response
    {
        return $this->deleteFile($entity, $postRequestHandler, 'getImg', 'setImg');
    }

    /**
     * @Route("/profil/avatar", name="api_attachment_profilavatar")
     */
    public function profilAvatar(UserRequestHandler $userRequestHandler): Response
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
     */
    public function userAvatar(User $entity, UserRequestHandler $userRequestHandler): Response
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
        $token = $this->requeststack->getCurrentRequest()->request->all('_token');

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
        $attachment = $entity->{$methodGet}();
        $this->deleteAttachment($attachment);
        $entity->{$methodSet}(null);
        $requesthandler->handle($old, $entity);
        $return['state'] = true;

        return new JsonResponse($return);
    }

    private function setDataAttachment($method)
    {
        $entity = $this->getRepository(Attachment::class)->{$method}();
        $return = [
            'state' => false,
            'error' => '',
        ];
        $token  = $this->verifToken($entity);
        if (!$token) {
            $return['error'] = 'Token incorrect';

            return new JsonResponse($return);
        }

        $this->deleteAttachment($entity);

        return new JsonResponse($return);
    }
}
