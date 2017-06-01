<?php

namespace App\Services;

use App\News;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class BingWebServiceCaller
{
    /**
     * @var
     */
    private $client;

    /**
     * @var string
     */
    private $url = 'https://api.cognitive.microsoft.com/bing/v5.0/search';

    /**
     * BingWebServiceCaller constructor.
     * @param $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * RE
     *
     * @param $source_url
     * @param $title
     * @return null
     */
    public function searchNews($source_url, $title)
    {
        $headers = [
            'Ocp-Apim-Subscription-Key' => env('BING_KEY', null),
        ];

        $parameters = [
            'q'          => $title,
            'count'      => '10',
            'offset'     => '0',
            'mkt'        => 'pt-br',
            'safesearch' => 'Moderate',
        ];

        try {
            $response = $this->client->request('GET', $this->url, [
                'headers' => $headers,
                'query'   => $parameters
            ]);

            if ($response->getStatusCode() != 200) {
                return null;
            }

            $news_id = null;
            $nDb = News::where('url', $source_url)->first();
            if ($nDb == null) {
                $news_id = News::insert(['url' => $source_url]);
                $news_id = $news_id->id;
            } else {
                $news_id = $nDb->id;
            }

            $news = json_decode($response->getBody()->getContents())->webPages->value;

            foreach ($news as $item) {
                $output = null;
                $item->probability = $this->compareStrings($item->name, $title);
                parse_str($item->url, $output);
                $item->originalUrl = $output['r'];
                $item->source_db_id = $news_id;
                if ($this->getDomainFromUrl($source_url) == $this->getDomainFromUrl($item->displayUrl)) {
                    //echo ' 1- '. $this->getDomainFromUrl($item->displayUrl) . ' igual <br>';
                    //echo ' 2- '. $this->getDomainFromUrl($source_url) . 'igual <br>';
                    unset($item);
                }
            }

            return $news;

        } catch (ClientException $ex) {
            return null;
        }

        return null;

    }

    private function compareStrings($s1, $s2)
    {

        //one is empty, so no result
        if (strlen($s1) == 0 || strlen($s2) == 0) {
            return 0;
        }

        $s1 = strtolower($s1);
        $s2 = strtolower($s2);

        //replace none alphanumeric charactors
        //i left - in case its used to combine words
        $s1clean = preg_replace("/[^A-Za-z0-9-]/", ' ', $s1);
        $s2clean = preg_replace("/[^A-Za-z0-9-]/", ' ', $s2);

        //remove double spaces
        while (strpos($s1clean, "  ") !== false) {
            $s1clean = str_replace("  ", " ", $s1clean);
        }
        while (strpos($s2clean, "  ") !== false) {
            $s2clean = str_replace("  ", " ", $s2clean);
        }

        //create arrays
        $ar1 = explode(" ", $s1clean);
        $ar2 = explode(" ", $s2clean);
        $l1 = count($ar1);
        $l2 = count($ar2);

        //flip the arrays if needed so ar1 is always largest.
        if ($l2 > $l1) {
            $t = $ar2;
            $ar2 = $ar1;
            $ar1 = $t;
        }

        //flip array 2, to make the words the keys
        $ar2 = array_flip($ar2);


        $maxwords = max($l1, $l2);
        $matches = 0;

        //find matching words
        foreach ($ar1 as $word) {
            if (array_key_exists($word, $ar2))
                $matches++;
        }

        return ($matches / $maxwords) * 100;
    }

    private function getDomainFromUrl($url)
    {
        $matches = null;
        if (preg_match('/^(?:https?:\/\/)?(?:[^@\/\n]+@)?(?:www\.)?([^:\/\n]+)/im', $url, $matches)) {
            $matches = $matches[1];
        }

        return $matches;
    }
}