<?php

namespace App\Controller;

use App\Service\RoyaleApiManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ClashRoyaleController extends AbstractController
{
    /**
     * @Route("/cr/find")
     * @param Request $request
     * @param RoyaleApiManager $royaleApiManager
     * @return JsonResponse|void
     */
    public function searchPlayer(
        Request $request,
        RoyaleApiManager $royaleApiManager
    )
    {
        $data = \json_decode($request->getContent(),true);

        if(!is_array($data) || count($data) === 0 || !array_key_exists('q',$data)){
            return new JsonResponse([
                'valid' => false,
                'error' => 'Invalid parameters'
            ]);
        }
        try{
            $result = $royaleApiManager->findByName($data);

            return new JsonResponse([
                'valid' => true,
                'data' => $result
            ]);
        } catch(\Exception $e){
            return new JsonResponse([
                'valid' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * @Route("/cr/current-deck")
     * @param Request $request
     * @param RoyaleApiManager $royaleApiManager
     * @return JsonResponse
     */
    public function getCurrentDeck(
        Request $request,
        RoyaleApiManager $royaleApiManager
    )
    {
        $data = \json_decode($request->getContent(),true);

        if(!is_array($data) || count($data) === 0 || !array_key_exists('tag',$data)){
            return new JsonResponse([
                'valid' => false,
                'error' => 'Invalid parameters'
            ]);
        }

        $result = json_decode($royaleApiManager->getCurrentDeck($data),true);

        return new JsonResponse([
            'valid' => true,
            'data' => $result['currentDeck'],
            'all' => $result
        ]);
    }
}
