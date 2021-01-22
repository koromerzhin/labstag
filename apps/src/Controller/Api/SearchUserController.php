<?php

namespace Labstag\Controller\Api;

use Labstag\Repository\UserRepository;
use Labstag\Service\PhoneService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/api")
 */
class SearchUserController extends AbstractController
{

    private PhoneService $phoneService;

    public function __construct(PhoneService $phoneService)
    {
        $this->phoneService = $phoneService;
    }

    /**
     * @Route("/search/user", name="api_search_user")
     *
     * @return Response
     */
    public function __invoke(Request $request, UserRepository $repository): Response
    {
        $get    = $request->query->all();
        $return = ['isvalid' => false];
        if (!array_key_exists('name', $get)) {
            return $this->json($return);
        }

        $users = $repository->findUserName($get['name']);
        $data  = [];

        foreach ($users as $user) {
            $data[] = [
                'id'   => $user->getId(),
                'name' => $user->getUsername(),
            ];
        }

        return $this->json($data);
    }
}