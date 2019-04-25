<?php

namespace App\Services;

use App\Models\RakutenApp\RakutenGenre;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;

/**
 * 楽天ジャンルAPI関連サービスクラス
 */
class RakutenGenreService
{
    /** @var RaktenGenre */
    private $rakutenGenre;
    /** @var HttpClient */
    private $httpClient;

    // 取得対象の階層を指定する
    const TARGET_RANGE_GENRE_LEVEL = 3;

    /**
     * RakutenGenreService constructor.
     * @param RakutenGenre $rakutenGenre
     * @param HttpClient $httpClient
     */
    public function __construct(
        RakutenGenre $rakutenGenre,
        HttpClient $httpClient
    )
    {
        $this->rakutenGenre = $rakutenGenre;
        $this->httpClient = $httpClient;
    }

    /**
     * 楽天ジャンル検索APIを利用してジャンル情報を取得する
     * @return array $genreList
     */
    public function getRakutenGenre()
    {
        // ジャンル情報取得
        $genreList = [];
        $parentGenreList = [];
        for ($i=0; $i<self::TARGET_RANGE_GENRE_LEVEL; $i++) {
            $getGenreListResult = $this->rakutenGenreApiExecute($parentGenreList);
            array_push($genreList, $getGenreListResult);
        }

        // 取得結果を平坦化
        $genreList = collect($genreList)->collapse()->toArray();
        return $genreList;
    }

    /**
     * 親のジャンルリストを元にジャンル情報の取得を行う
     * @param array $parentGenreList
     * @return array
     */
    private function rakutenGenreApiExecute($parentGenreList)
    {
        $result = [];
        $method = config('rakuten.genreApi.method');

        // 1階層目
        if (empty($parentGenreList)) {
            // url生成
            $apiUrl = config('rakuten.genreApi.apiUrl');
            $url = sprintf($apiUrl['baseUrl'], $apiUrl['applicationId'], $apiUrl['formatVersion'], $apiUrl['elements'], $apiUrl['defaultGenreId']);
            // データ取得
            $apiExecuteResult = $this->apiExecute($method, $url);
            if (!$apiExecuteResult['resultStatus']) {
                return $result;
            }
            // データ整形
            $result = $this->formatGenreList($apiExecuteResult['response']);
            return $result;
        }
        // 2階層目以降
        foreach ($parentGenreList as $parentGenreData) {
            // url生成
            $apiUrl = config('rakuten.genreApi.apiUrl');
            $url = sprintf($apiUrl['baseUrl'], $apiUrl['applicationId'], $apiUrl['formatVersion'], $apiUrl['elements'], $parentGenreData['genreId']);
            // データ取得
            $apiExecuteResult = $this->apiExecute($method, $url);
            if (!$apiExecuteResult['resultStatus']) {
                return $result;
            }
            // データ整形
            $formatGenreList = $this->formatGenreList($apiExecuteResult['response']);
            foreach ($formatGenreList as $formatGenreData) {
                array_push($result, $formatGenreData);
            }
        }
        return $result;
    }

    /**
     * API実行結果から必要な情報を取り出し整形する
     * @param array $genreList
     * @return array
     */
    private function formatGenreList($genreList)
    {
        $result = [];
        if (!array_key_exists('children', $genreList)) {
            return $result;
        }
        $parentId = $genreList['current']['genreId'];
        foreach ($genreList['children'] as $genreData) {
            $formatData = [
                'genreId'       => $genreData['genreId'],
                'genreName'     => $genreData['genreName'],
                'genreLevel'    => $genreData['genreLevel'],
                'parentId'      => $parentId,
            ];
            array_push($result, $formatData);
        }
        return $result;
    }

    /**
     * 楽天ジャンル情報の登録を行う
     * テーブルにデータが存在している際は全件削除後に登録処理を行う
     * @param array $genreList
     * @return boolean $result
     */
    public function storeRakutenGenre($genreList)
    {
        $result = false;
        $formatGenreList = $this->formatStoreGenreList($genreList);

        // 削除処理
        $countRakutenGenreResult = $this->rakutenGenre->count();
        if ($countRakutenGenreResult >= 1) {
            $deleteRakutenGenreResult = $this->rakutenGenre->deleteAll();
            if (!$deleteRakutenGenreResult) {
                return $result;
            }
        }

        // 登録処理
        $storeRakutenGenreResult = $this->rakutenGenre->insertRakutenGenre($formatGenreList);
        if (!$storeRakutenGenreResult) {
            return $result;
        }

        $result = true;
        return $result;
    }

    /**
     * 登録用にデータの整形を行う
     * @param array $genreList
     * @return array $result
     */
    private function formatStoreGenreList($genreList)
    {
        $result = [];
        foreach ($genreList as $genreData) {
            $formatData = [
                'genre_id'       => $genreData['genreId'],
                'genre_name'     => $genreData['genreName'],
                'genre_level'    => $genreData['genreLevel'],
                'parent_id'      => $genreData['parentId'],
            ];
            array_push($result, $formatData);
        }
        return $result;
    }

    /**
     * 配列を元にツリー構造のデータを生成する
     * @param array $genreList
     * @return array $tree
     */
    public function createTree($genreList)
    {
        $tree = array();
        $index = array();
        foreach ($genreList as $genreData) {
            $genreId = $genreData['genreId'];
            $parentId = $genreData['parentId'];
            // 親子関係にあるデータを紐づける
            if (isset($index[$genreId])) {
                $genreData['children'] = $index[$genreId]['children'];
                $index[$genreId] = $genreData;
            } else {
                $index[$id] = $genreData;
            }

            if ($parentId == 0) {
                $tree[] = & $index[$genreId];
            } else {
                $index[$parentId]["children"][] = & $index[$genreId];
            }
        }
        return $tree;
    }

    /**
     * API実行処理
     * @param string $method
     * @param string $url
     * @param array $options
     * @return array $result
     */
    private function apiExecute($method, $url, $options = [])
    {
        $result = [
            'resultStatus'  => false,
            'response'      => [],
        ];
        // API実行処理
        try {
            $response = $this->httpClient->request($method, $url, $options);
            $result['response'] = json_decode($response->getBody()->getContents(), true);
            $result['resultStatus'] = true;
        } catch (RequestException $e) {
            // TODO API実行エラー、ログ出力などが必要であればここに記載
        }
        return $result;
    }
}
