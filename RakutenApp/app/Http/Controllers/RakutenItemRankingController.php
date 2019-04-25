<?php
namespace App\Http\Controllers;

use App\Services\RakutenGenreService;
use App\Services\RakutenItemRankingService;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

/**
 * 楽天商品ランキングAPI関連処理クラス
 */
class RakutenItemRankingController extends Controller
{
    protected $RakutenItemRankingService;

    /**
     * RakutenGenreController constructor.
     * @param RakutenItemRankingService $RakutenItemRankingService
     */
    public function __construct(RakutenItemRankingService $RakutenItemRankingService)
    {
        $this->RakutenItemRankingService = $RakutenItemRankingService;
    }

    /**
     * 楽天商品ランキングAPI関連処理
     */
    public function index()
    {
        // 処理結果初期定義
        $result = [
            'getRakutenGenreListResult' => 'failure',
            'getRakutenItemRanking' => 'failure',
            'storeRakutenItemRanking' => 'failure',
        ];

        // DBからジャンル情報を取得
        $rakutenGenreList = $this->RakutenItemRankingService->getRakutenGenreList();
        if (empty($rakutenGenreList)) {
            return $result;
        }
        $result['getRakutenGenreListResult'] = 'success';

        // 楽天APIを利用して商品ランキング情報を取得
        $getRakutenItemRanking = $this->RakutenItemRankingService->getRakutenItemRanking($rakutenGenreList);
        if (empty($getRakutenItemRanking)) {
            return $result;
        }
        $result['getRakutenItemRanking'] = 'success';

        // 取得した情報をDBに登録
        $storeRakutenItemRankingResult = $this->RakutenItemRankingService->storeRakutenItemRanking($getRakutenItemRanking);
        if (!$storeRakutenItemRankingResult) {
            return $result;
        }
        $result['storeRakutenItemRanking'] = 'success';

        return $result;
    }
}
