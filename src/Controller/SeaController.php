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
$denug = [];
$nbBloc = 0;
        foreach($dataTide as $index=>$node){
            $isType = $index % 3 === 0;
            $isHour = $index % 3 === 1;
            if($isType){
                $nbBloc++;
                $tmp = [
                    'type' => $node->nodeValue,
                    'heure' => ''
                ];
            }
            if($isHour){
                $hourArr = explode(':',$node->nodeValue);
                list($hour,$minute) = $hourArr;
                if($nbBloc === 1 && $hour = 12){
                    $hour = 0;
                } elseif($nbBloc > 2 && $hour < 12){
                    $hour += 12;
                }
                $tmp['heure'] = $hour.':'.$minute;
                $debug[] = $tmp;
            }
            //$debug[] = $node->nodeValue;
        }
        dump($debug);

        if($isAfterNoon){
            $hauteMaree = trim($dataTide->eq(7)->text());
            if($dataTide->count()>10){
                $basseMaree = trim($dataTide->eq(10)->text());
            } else {
                $basseMaree = trim($dataTide->eq(4)->text());
            }
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

        $infoMaree = $this->getTideInfo($basseMaree,$hauteMaree);

        $debug = [];
//        foreach ($dataTide as $domElement) {
//            $debug[] = $domElement->nodeValue;
//        }
        return new JsonResponse([
            'status' => 'success',
            'wave_min' => $waveMin,
            'wave_max' => $waveMax,
            'maree_haute' => $hauteMaree,
            'maree_basse' => $basseMaree,
            'sens_maree' => $infoMaree['sens_maree'],
            'ratio_maree' => $infoMaree['ratio_maree'],
            'next_maree' => $infoMaree['next_maree'],
            'ville' => 'Guidel'
        ]);
    }

    private function getTideInfo($basseMaree,$hauteMaree)
    {
        $hauteMareeOn5 = substr('0'.$hauteMaree,-5);
        $basseMareeOn5 = substr('0'.$basseMaree,-5);

        $basseMareeFirst = $hauteMareeOn5 > $basseMareeOn5;

        $now = date('H:i');

        if($basseMareeFirst){
            if($now < $basseMareeOn5){
                $sens = 'down';
                $heureFin = $basseMaree;
                $offset = false;
                $nextMaree = $basseMaree;
            } elseif($now < $hauteMareeOn5){
                $sens = 'up';
                $heureFin = $hauteMaree;
                $offset = false;
                $nextMaree = $hauteMaree;
            } else {
                $sens = 'down';
                $heureFin = $basseMaree;
                $offset = true;
            }
        } else {
            if($now < $hauteMareeOn5){
                $sens = 'up';
                $heureFin = $hauteMaree;
                $offset = false;
                $nextMaree = $hauteMaree;
            } elseif ($now < $basseMareeOn5){
                $sens = 'down';
                $heureFin = $basseMaree;
                $offset = false;
                $nextMaree = $basseMaree;
            } else {
                $sens = 'up';
                $heureFin = $hauteMaree;
                $offset = true;
            }
        }
        $heureFinSplit = explode(':',$heureFin);
        $flatHour = $heureFinSplit[0]*60 + $heureFinSplit[1];

        if($offset){
            // Dans ce cas on ajoute 12h20
            $flatHour += 12*60 + 20;
            $minute = $flatHour % 60;
            $heure = (($flatHour - $minute) / 60) % 24;
            $heureFin = $heure.':'.substr('0'.$minute,-2);
            $nextMaree = $heureFin;
        }

        $nowFlat = date('H')*60 + date('i');

        $difference = $flatHour - $nowFlat;
        $ratio = 360 - round(((370 - $difference) / 370) * 360,0);

        return [
            'sens_maree' => $sens,
            'ratio_maree' => $ratio,
            'next_maree' => $nextMaree
        ];
    }
}
