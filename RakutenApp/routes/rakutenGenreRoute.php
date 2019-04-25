<?php

use Illuminate\Http\Request;

Route::group([
    'prefix' => 'rakuten/genre',
], function () {

    Route::get('/',[
        'as' => 'RakutenGenre.index',
        'uses' => 'RakutenGenreController@index'
    ]);
});
