<?php

namespace App\Service;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class RoyaleApiManager
{
    private $crToken;
    public function __construct($crToken)
    {
        $this->crToken = $crToken;
    }

    public function findByName(
        array $data
    )
    {
        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header'=> "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );

        $context = stream_context_create($opts);

        $url = 'https://royaleapi.com/player/search/results?q='.$data['q'];
        $content = file_get_contents($url,false,$context);

        $crawler = new Crawler($content);

        $liste = $crawler->filter('div.player_search_results__result_container');

        $return = [];

        $liste->each(function(Crawler $user,$index) use (&$return){
            $tmp = $user->filter('div')->eq(2);
            file_put_contents(__DIR__.'/../../var/log/cr.txt',count($tmp).' - '.$tmp->text());
            /*
                <div>
                    <a class="header" href="/player/989JJP09Y">
                        AshtaxYT
                    </a>
                    <div class="player_tag">#989JJP09Y</div>
                    <a href="/clan/QQCRLGPU" class="meta">
                        kaiju bricks yt&nbsp;&nbsp;#QQCRLGPU
                    </a>
                </div>

             */
            $listeA = $tmp->filter('a');
            $return[] = [
                'player' => $listeA->eq(0)->text(),
                'clan' => $listeA->eq(1)->text()
            ];
        });

        return ['nb' => count($liste)];
/*
        $hrefList = $crawler->filter('tr.result > td');

        $return = [];
        foreach($hrefList as $index=>$node){
            if(count($node->childNodes) >= 3){
                $player = str_replace([chr(10),chr(13)],'', $node->childNodes[1]->nodeValue);
                $playerTag = $node->childNodes[3]->nodeValue;
                dump($node->childNodes);
                $clan = count($node->childNodes) >= 6 ?
                    trim(strstr(str_replace([chr(10),chr(13)],'',$node->childNodes[5]->nodeValue),'#',true)):
                    'Pas de clan';

                $return[] = [
                    'player' =>$player,
                    'tag' => $playerTag,
                    'clan' => $clan
                ];
            }
        }
        return $return;
        */
    }

    public function getCurrentDeck($data)
    {
        $url = 'https://api.clashroyale.com/v1/players/%23'.substr($data['tag'],1);

        $options = array('http' => array(
            'method'  => 'GET',
            'header' => 'Authorization: Bearer '.$this->crToken
        ));
        $context  = stream_context_create($options);

        return file_get_contents($url,false,$context);
    }
}
