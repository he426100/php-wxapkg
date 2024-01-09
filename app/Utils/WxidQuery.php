<?php

declare(strict_types=1);

namespace App\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WxidQuery
{
    public static function query(string $wxid): WxidInfo
    {
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

        return new WxidInfo([
            ...$data['data'],
            'wxid' => $wxid,
        ]);
    }
}
