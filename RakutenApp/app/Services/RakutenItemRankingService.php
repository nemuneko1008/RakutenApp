<?php

namespace App\Services;

use App\Models\RakutenApp\RakutenGenre;
use App\Models\RakutenApp\RakutenItemRanking;

/**
 * Class RakutenItemRankingService
 * @package App\Services
 */
class RakutenItemRankingService
{
    /** @var ApiExecuteService */
    private $apiExecuteService;
    /** @var RaktenGenre */
    private $rakutenGenre;
    /** @var RaktenGenre */
    private $rakutenItemRanking;

    /**
     * RakutenItemRankingService constructor.
     * @param RakutenGenre $rakutenGenre
     * @param RakutenItemRanking $rakutenItemRanking
     */
    public function __construct(
        ApiExecuteService $apiExecuteService,
        RakutenGenre $rakutenGenre,
        RakutenItemRanking $rakutenItemRanking
    )
    {
        $this->apiExecuteService = $apiExecuteService;
        $this->rakutenGenre = $rakutenGenre;
        $this->rakutenItemRanking = $rakutenItemRanking;
    }

    /**
     * DBから楽天ジャンル情報を取得する
     * @return array $genreList
     */
    public function getRakutenGenreList()
    {
        // ジャンル情報取得
        $getRakutenGenreList = $this->rakutenGenre->getAll();
        return $getRakutenGenreList;
    }

    /**
     * 楽天商品ランキングAPIを利用してランキング情報を取得する
     * @param $genreList
     * @return array
     */
    public function getRakutenItemRanking($genreList)
    {
        $result = [];
        $method = config('rakuten.itemRankingApi.method');
        foreach ($genreList as $genreData) {
            $apiUrl = config('rakuten.itemRankingApi.apiUrl');
            $url = sprintf($apiUrl['baseUrl'], $apiUrl['applicationId'], $apiUrl['formatVersion'], $apiUrl['elements'], $genreData->genre_id);
            // データ取得
            $apiExecuteResult = $this->apiExecuteService->apiExecute($method, $url);
            // FIXME 「genreID=100000」 の際にエラーが発生しているため取得失敗時も処理を継続させている
            if ($apiExecuteResult['resultStatus']) {
                $apiExecuteResult['response']['genreId'] =  $genreData->genre_id;
                array_push($result, $apiExecuteResult['response']);
            }

            // TODO リクエスト制限対策、要調整
            sleep(1);
        }
        return $result;
    }

    /**
     * 楽天商品ランキング情報の登録を行う
     * @param array $itemRankingresult
     * @return boolean $result
     */
    public function storeRakutenItemRanking($itemRankingResult)
    {
        $result = false;
        $formatItemRankingList = [];
        // データを登録用に整形
        foreach ($itemRankingResult as $itemRankingList){
            array_push($formatItemRankingList, $this->formatStoreItemRankingList($itemRankingList));
        }

        // データを平坦化
        $formatItemRankingList = collect($formatItemRankingList)->collapse()->toArray();

        // 削除処理
        $countRakutenItemRankingResult = $this->rakutenItemRanking->count();
        if ($countRakutenItemRankingResult >= 1) {
            $deleteRakutenItemRankingResult = $this->rakutenItemRanking->deleteAll();
            if (!$deleteRakutenItemRankingResult) {
                return $result;
            }
        }

        // 登録処理
        $storeRakutenItemRankingResult = $this->rakutenItemRanking->insertRakutenItemRanking($formatItemRankingList);
        if (!$storeRakutenItemRankingResult) {
            return $result;
        }

        $result = true;
        return $result;
    }

    /**
     * 楽天商品ランキングを登録用に整形する
     * @param array $itemRankingList
     * @return array $result
     */
    private function formatStoreItemRankingList($itemRankingList)
    {
        $result = [];
        $genreId = $itemRankingList['genreId'];
        foreach ($itemRankingList['Items'] as $itemRankingData) {
            if ($itemRankingData['rank'] > 10) {
                break;
            }
            $formatData = [
                'genre_id'  => $genreId,
                'item_name' => $itemRankingData['itemName'],
                'rank'      => $itemRankingData['rank'],
            ];
            array_push($result, $formatData);
        }
        return $result;

    }
}
