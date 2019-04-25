<?php
namespace App\Models\RakutenApp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 楽天商品ランキングテーブル
 */
class RakutenItemRanking extends Model
{
    /**
     * 件数カウント
     * @return integer
     */
    public function count()
    {
        return DB::table('rakuten_item_ranking')->count();
    }

    /**
     * 新規登録
     * @param array $params
     * @return boolean
     */
    public function insertRakutenItemRanking($params)
    {
        return DB::table('rakuten_item_ranking')->insert($params);
    }

    /**
     * 全件削除
     * @return boolean
     */
    public function deleteAll()
    {
        return DB::table('rakuten_item_ranking')->delete();
    }
}
