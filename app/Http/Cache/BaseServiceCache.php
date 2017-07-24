<?php
namespace App\Http\Cache;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Log;

class BaseServiceCache
{
    public function __construct()
    {
        
    }

    public function setPrtefix($String = 'default')
    {
        return Cache::store('redis')->setPrefix($String);
    }

    public function getBaseId(string $tags)
    {
        if (empty($tags)) {
            throw new \Exception('tags can not null');
        }
        return $tags . '_' . uniqid();
    }

    public function setListCache(string $name, string $type, string $value)
    {
        if (empty($name) || empty($type) || empty($value)) {
            throw new \Exception('Set List Cache Error');
        }
        return Redis::rpush($name . '_' . $type, $value);
    }

    public function setTagsCache(string $name, string $type, string $id, $data = array())
    {
        if (empty($name) || empty($type) || empty($id)) {
            throw new \Exception('Set Tags Cache Error');
        }
        return Cache::store('redis')
            ->tags([$name . '_' . $type])
            ->forever($id, $data);
    }


}