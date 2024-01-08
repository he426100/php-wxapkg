<?php

declare(strict_types=1);

namespace App\Utils;

class WxidInfo
{
    public string $wxid = '';
    public string $appid = '';
    public string $username = '';
    public string $nickname = '';
    public string $description = '';
    public string $avatar = '';
    public string $category = '';
    public string $principal_name = '';
    public $uses_count = 0;
    public string $keyword = '';
    public string $auth_list = '';
    public string $plugins_list = '';
    public string $domain_list = '';
    public $register_at = 0;
    public $upgrade_at = 0;
    public $created_at = 0;
    public string $location = '';
    public string $error = '';

    public function __construct(array $info)
    {
        array_map(fn ($k, $v) => $this->{$k} = $v, array_keys($info), $info);
    }

    public function toJson(): string
    {
        return json_encode($this, JSON_PRETTY_PRINT);
    }
}
