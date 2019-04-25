<?php
namespace App\Models\RakutenApp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 楽天ジャンルテーブル
 */
class RakutenGenre extends Model
{
    /**
     * 全件取得
     * @return object
     */
    public function getAll()
    {
        return DB::table('rakuten_genre')->get();
    }

    /**
     * レコード数カウント
     * @return integer
     */
    public function count()
    {
        return DB::table('rakuten_genre')->count();
    }

    /**
     * 新規登録
     * @param array $params
     * @return boolean
     */
    public function insertRakutenGenre($params)
    {
        return DB::table('rakuten_genre')->insert($params);
    }

    /**
     * 全件削除
     * @return boolean
     */
    public function deleteAll()
    {
        return DB::table('rakuten_genre')->delete();
    }
}
