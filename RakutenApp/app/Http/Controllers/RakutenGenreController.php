<?php
namespace App\Http\Controllers;

use App\Services\RakutenGenreService;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

/**
 * 楽天ジャンルAPI関連処理クラス
 */
class RakutenGenreController extends Controller
{
    protected $RakutenGenreService;

    /**
     * RakutenGenreController constructor.
     * @param RakutenGenreService $RakutenGenreService
     */
    public function __construct(RakutenGenreService $RakutenGenreService)
    {
        $this->RakutenGenreService = $RakutenGenreService;
    }

    /**
     * 楽天ジャンルAPI関連処理
     */
    public function index()
    {
        // 処理結果初期定義
        $result = [
            'getRakutenGenreResult' => 'failure',
            'storeRakutenGenreResult' => 'failure',
            'createRakutenGenreTreeResult' => [],
        ];

        // 楽天APIを利用してジャンル情報を取得
        $genreList = $this->RakutenGenreService->getRakutenGenre();
        if (empty($genreList)) {
            return $result;
        }
        $result['getRakutenGenreResult'] = 'success';

        // 取得した情報をテーブルに保存
        $storeRakutenGenreResult = $this->RakutenGenreService->storeRakutenGenre($genreList);
        if ($storeRakutenGenreResult) {
            $result['storeRakutenGenreResult'] = 'success';
        }

        // 取得した情報をツリー構造に再構成
        $createRakutenGenreTreeResult = $this->RakutenGenreService->createTree($genreList);
        $result['createRakutenGenreTreeResult'] = $createRakutenGenreTreeResult;

        return $result;
    }
}
