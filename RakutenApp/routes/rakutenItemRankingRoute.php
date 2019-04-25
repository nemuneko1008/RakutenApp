<?php

use Illuminate\Http\Request;

Route::group([
    'prefix' => 'rakuten/item/ranking',
], function () {

    Route::get('/',[
        'as' => 'RakutenGenre.index',
        'uses' => 'RakutenItemRankingController@index'
    ]);
});
