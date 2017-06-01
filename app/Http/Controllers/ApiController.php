<?php

namespace App\Http\Controllers;

use App\News;
use App\Services\MetaServiceCaller;
use App\Services\BingWebServiceCaller;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * @var MetaServiceProvider
     */
    private $metaProvider;

    /**
     * @var BingWebServiceCaller
     */
    private $newsProvider;

    /**
     * ExternalController constructor.
     * @param MetaServiceCaller $metaProvider
     * @param BingWebServiceCaller $newsProvider
     */
    public function __construct(MetaServiceCaller $metaProvider, BingWebServiceCaller $newsProvider)
    {
        $this->metaProvider = $metaProvider;
        $this->newsProvider = $newsProvider;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function call(Request $request)
    {
        $url = $request->input('url');

        $tags = $this->metaProvider->fetchMetaTags($url);
        if(is_string($tags)) {
            return response()->json(['status' => 505, 'data' => $tags]);
        }

        if ( ! count($tags)) {
            return response()->json(['status' => 505, 'data' => 'This website don\'t use any kind of meta-tags']);
        }

        $news = null;
        if (array_key_exists("twitter:title", $tags)) {
            $news = $this->newsProvider->searchNews($url, $tags['twitter:title']);
        } else if (array_key_exists("description", $tags)) {
            $news = $this->newsProvider->searchNews($url, $tags['description']);
        } else {
            return response()->json(['status' => 505, 'data' => 'This website don\'t use any kind of meta-tags']);
        }

        $average = null;

        if ( ! is_null($news)) {
            $news = [
                'current_new' => News::find($news[0]->source_db_id)->toArray(),
                'data'        => $news
            ];

            return response()->json(['status' => 500, 'data' => $news]);
        }
    }

    public function callGET(Request $request)
    {
        $url = "http://www.jn.pt/local/noticias/lisboa/lisboa/interior/transito-cortado-na-ponte-25-de-abril-apos-acidente-com-feridos-5679922.html";

        try {
            $tags = $this->metaProvider->fetchMetaTags($url);
            if ( ! count($tags)) {
                return response()->json(['status' => 505, 'data' => 'This website don\'t use any kind of meta-tags']);
            }
        }
        catch(RequestException $e) {
            return response()->json(['status' => 505, 'data' => $e->getMessage()]);
        }


        $news = null;
        if (array_key_exists("twitter:title", $tags)) {
            $news = $this->newsProvider->searchNews($url, $tags['twitter:title']);
        } else if (array_key_exists("description", $tags)) {
            $news = $this->newsProvider->searchNews($url, $tags['description']);
        } else {
            return response()->json(['status' => 505, 'data' => 'This website don\'t use any kind of meta-tags']);
        }

        $average = null;

        $news = [
            'current_new' => News::find($news[0]->source_db_id)->toArray(),
            'data'        => $news
        ];
        dd($news);
        if ( ! is_null($news)) {
            foreach ($news as $item) {
                $average += $item->probability;
                echo 'Title:' . $item->name . 'Fonte: ' . $item->displayUrl . ' Probabilidade: ' . $item->probability . '<br>';
            }
            echo '<br><br>Total de probabilidade de ser verdadeira: ' . round($average / count($news));
            //return response()->json(['status' => 500, 'data' => $news]);
        }
    }

    public function upvote(Request $request)
    {
        $id = $request->input('id');
        if ($id != null) {
            $new = News::find($id);
            if ($new != null) {
                $new->upvotes++;
                if ($new->save()) {
                    return response()->json(['status' => 500, 'data' => [
                        'upvotes' => $new->upvotes,
                        'downvotes' => $new->downvotes,
                    ]]);
                }
            }
        }

        return response()->json(['status' => 505, 'data' => 'Not worked!']);
    }

    public function downvote(Request $request)
    {
        $id = $request->input('id');
        if ($id != null) {
            $new = News::find($id);
            if ($new != null) {
                $new->downvotes++;
                if ($new->save()) {
                    return response()->json(['status' => 500, 'data' => [
                        'upvotes' => $new->upvotes,
                        'downvotes' => $new->downvotes,
                    ]]);
                }
            }
        }

        return response()->json(['status' => 505, 'data' => 'Not worked!']);
    }
}
