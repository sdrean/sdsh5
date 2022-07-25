<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SeaController extends AbstractController
{
    /**
     * @Route("/wave")
     * @return JsonResponse
     */
    public function getData()
    {
        $url = 'https://fr.magicseaweed.com/Guidel-Les-Kaolins-Surf-Report/71/';
        $crawler = new Crawler(file_get_contents($url));
        $crawlerWave = $crawler->filter('li.rating-text');
        $waveMin = '--';
        $waveMax = '--';
        if($crawler->count() === 1){
            $waveStr = trim($crawlerWave->first()->text());
            preg_match('/^(.*)\-(.*)m$/',$waveStr,$output);
            if(count($output) === 3){
                $waveMin = $output[1];
                $waveMax = $output[2];
            }
        }
        $crawlerTide = $crawler->filter('table.table-tide');
        // On prend le premier noeud (table)
        $tideNode = $crawlerTide->first();
        $dataTide = $tideNode->filter('td');
        $hauteMaree = '--';
        $basseMaree = '--';
        $isAfterNoon = date('H') > 11;

        if($isAfterNoon){
            $hauteMaree = trim($dataTide->eq(7)->text());
            $basseMaree = trim($dataTide->eq(10)->text());
            // on va maintenant ajouter 12 h car data récupérées au format 12h et non 24h
            $hauteMareeArr = explode(':',$hauteMaree);
            if(intval($hauteMareeArr[0]) < 12){
                $hauteMaree = (intval($hauteMareeArr[0])+12).':'.$hauteMareeArr[1];
            }
            $basseMareeArr = explode(':',$basseMaree);
            if(intval($basseMareeArr[0]) < 12){
                $basseMaree = (intval($basseMareeArr[0])+12).':'.$basseMareeArr[1];
            }
        } else {
            $hauteMaree = trim($dataTide->eq(1)->text());
            $basseMaree = trim($dataTide->eq(4)->text());
        }
        $debug = [];
//        foreach ($dataTide as $domElement) {
//            $debug[] = $domElement->nodeValue;
//        }
        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'wave_min' => $waveMin,
                'wave_max' => $waveMax,
                'maree_haute' => $hauteMaree,
                'maree_basse' => $basseMaree,
                'ville' => 'Guidel'
            ]
        ]);
    }
}
