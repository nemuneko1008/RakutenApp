<?php

return [
    'method' => 'GET',
    'apiUrl' => [
        'baseUrl'           => 'https://app.rakuten.co.jp/services/api/IchibaGenre/Search/20140222?applicationId=%s&formatVersion=%s&elements=%s&genreId=%s',
        'applicationId'     => '1026301013779899297',
        'formatVersion'     => '2',
        'elements'          => 'genreId,genreName,genreLevel',
        'defaultGenreId'    => '0',
    ],
];
