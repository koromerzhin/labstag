<?php

namespace Labstag\Controller\Api;

use Labstag\Lib\ApiControllerLib;
use Labstag\Service\PhoneService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/check')]
class CheckController extends ApiControllerLib
{
    #[Route(path: '/phone', name: 'api_check_phone')]
    public function phone(
        PhoneService $phoneService,
        Request $request
    ): Response
    {
        $get    = $request->query->all();
        $return = ['isvalid' => false];
        if (!array_key_exists('country', $get) || !array_key_exists('phone', $get)) {
            return $this->json($return);
        }

        $phone   = $request->query->get('phone');
        $country = $request->query->get('country');

        $verif = $phoneService->verif(
            (string) $phone,
            (string) $country
        );
        $return['isvalid'] = array_key_exists('isvalid', $verif) ? $verif['isvalid'] : false;

        return $this->json($return);
    }
}
