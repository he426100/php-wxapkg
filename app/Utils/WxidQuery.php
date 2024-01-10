<?php

declare(strict_types=1);

namespace App\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WxidQuery
{
    private array $cache = [];
    private const CACHE_PATH = 'runtime/wxid.json';

    public function __construct()
    {
        $this->loadCache();
    }

    public function query(string $wxid): WxidInfo
    {
        if (isset($this->cache[$wxid])) {
            return $this->cache[$wxid];
        }

        $client = new Client();
        $body = ['appid' => $wxid];
        $headers = [
            'Content-Type' => 'application/json;charset=utf-8',
        ];

        try {
            $response = $client->post('https://kainy.cn/api/weapp/info/', ['json' => $body, 'headers' => $headers]);
            $data = json_decode((string) $response->getBody(), true);
        } catch (GuzzleException $e) {
            return new WxidInfo(['wxid' => $wxid, 'error' => $e->getMessage()]);
        }
        if ($data['code'] !== 0) {
            return new WxidInfo(['wxid' => $wxid, 'error' => $data['errors']]);
        }

        $this->cache[$wxid] = new WxidInfo([
            ...$data['data'],
            'wxid' => $wxid,
        ]);

        $this->saveCache();

        return $this->cache[$wxid];
    }

    private function loadCache()
    {
        if (file_exists(self::CACHE_PATH)) {
            $data = file_get_contents(self::CACHE_PATH);
            $decodedData = json_decode($data, true);
            $this->cache = array_map(WxidInfo::class . '::fromArray', $decodedData ?: []);
        }
    }

    private function saveCache()
    {
        $jsonData = json_encode($this->cache);
        file_put_contents(self::CACHE_PATH, $jsonData);
    }
}
