<?php
namespace App\Http\Cache;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Log;

class BaseCacheHelper
{
    const SENDLISTLIMIT = 200;

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

    public function setListCache(string $tags, string $type, string $value)
    {
        if (empty($tags) || empty($type) || empty($value)) {
            throw new \Exception('Set List Cache Error');
        }

        return Redis::rpush($tags . '_' . $type, $value);
    }

    public function setTagsCache(string $tags, string $type, string $id, $data = array())
    {
        if (empty($tags) || empty($type) || empty($id)) {
            throw new \Exception('Set Tags Cache Error');
        }

        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forever($id, $data);
    }

    public function getListCache(string $tags, string $type)
    {
        if (empty($tags) || empty($type)) {
            throw new \Exception('Get List Cache Error');
        }

        return Redis::lrange($tags . '_' . $type, 0, self::SENDLISTLIMIT);
    }

    public function getCacheValue(string $tags, string $type, string $id)
    {
        if (empty($tags) || empty($type) || empty($id)) {
            throw new \Exception('Get Cache Value Error');
        }

        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->get($id);
    }
    /**
     * delete redis list value
     * @param [String] $tags 支付別名
     * @param [String] $type 組別
     * @param [String] $value 
     * @return boolean
     */  
    public function deleteListValue($tags, $type, $value)
    {
        if (empty($tags) || empty($type) || empty($value)) {
            throw new \Exception('Delete Cache List Value Error');
        }

        return Redis::LREM($tags . '_' . $type, 0, $value);
    }
    /**
     * delete redis tags data
     * @param [String] $tags 支付別名
     * @param [String] $type 組別
     * @param [String] $value 
     * @return boolean
     */  
    public function deleteTagsValue($tags, $type, $value)
    {
        if (empty($tags) || empty($type) || empty($value)) {
            throw new \Exception('Delete Cache Value Error');
        }

        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forget($value);
    }
}