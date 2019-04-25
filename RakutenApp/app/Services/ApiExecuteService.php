<?php

namespace App\Services;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;

/**
 * Class ApiExecuteService
 * @package App\Services
 */
class ApiExecuteService
{
    /** @var HttpClient */
    private $httpClient;

    /**
     * ApiExecuteService constructor.
     * @param HttpClient $httpClient
     */
    public function __construct(
        HttpClient $httpClient
    )
    {
        $this->httpClient = $httpClient;
    }

    /**
     * API実行処理
     * @param string $method
     * @param string $url
     * @param array $options
     * @return array $result
     */
    public function apiExecute($method, $url, $options = [])
    {
        $result = [
            'resultStatus' => false,
            'response' => [],
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
