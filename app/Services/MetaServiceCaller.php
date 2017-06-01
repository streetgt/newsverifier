<?php

namespace App\Services;

use Goutte as Goutte;
use GuzzleHttp as GuzzleHttp;

class MetaServiceCaller
{
    /**
     * @param null $url
     * @return array
     */
    public function fetchMetaTags($url = null)
    {
        $HTTPconfig = ["curl" => [
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ],
            ['http_errors' => true]
        ];
        try {
            $HTTPclient = new Goutte\Client;
            $HTTPclient->setClient(new GuzzleHttp\Client($HTTPconfig));
            $HTTPclient->setHeader('user-agent', 'Mozilla/5.0 (Windows NT 6.2; rv:20.0) Gecko/20121202 Firefox/20.0');
            $request = $HTTPclient->getClient()->request('GET', $url);
        } catch (RequestException $e) {
            return $e->getMessage();
        }
        catch (GuzzleHttp\Exception\ConnectException $e) {
            return 'Invalid Website!';
        }

        $crawler = Goutte::request('GET', $url);

        $meta_tags = $crawler->filterXpath('//meta')->each(function ($node) {
            foreach ($node->extract(['name', 'content']) as $item) {
                $flag = 0;
                foreach ($item as $value) {
                    if (empty($value) || $value == '') {
                        $flag = 1;
                    }
                }

                if ($flag == 0) {
                    return [
                        $item[0] => $item[1]
                    ];
                }
            }
        });

        $meta_tags = array_values(array_filter($meta_tags));
        $meta_tags = $this->findKeys($meta_tags, ['description', 'twitter:description', 'twitter:title']);

        return $meta_tags;
    }

    private function findKeys($array, array $keysSearch)
    {
        $results = [];

        foreach ($array as $key => $val) {
            foreach ($keysSearch as $needle) {
                if (array_key_exists($needle, $val)) {
                    $results[key($val)] = $val[key($val)];
                }
            }
        }

        if ( ! count($results)) {
            return 0;
        }

        return $results;
    }
}