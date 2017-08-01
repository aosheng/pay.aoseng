<?php
namespace App\Http\Cache;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Log;
class BaseCacheHelper
{
    const SENDLISTLIMIT = 200;
    const ZADDLISTLIMIT = 100;

    public function setPrtefix($String = 'default')
    {
        return Cache::store('redis')->setPrefix($String);
    }
    /**
     * 取得唯一key
     * @param string $tags 支付別名
     * @return string
     */
    public function getBaseId(string $tags)
    {
        if (empty($tags)) {
            throw new \Exception('tags can not null');
        }

        return $tags . '_' . uniqid();
    }
    /**
     * 紀錄有序的資料
     * @param string $tags 支付別名
     * @param string $type 組別
     * @param int $value 
     * @param string $id 唯一key
     * @return void
     */
    public function setZaddCach(string $tags, string $type, int $value, string $id)
    {
        if (empty($tags) || empty($type) || empty($value) || empty($id)) {
            throw new \Exception('Zadd Cache Error');
        }

        Redis::zadd($tags . '_' . $type, $value, $id);
    }
    /**
     * 取得已存入陣列
     * @param string $tags 支付別名
     * @param string $type 組別
     * @param array $options 
     * @return array
     */
    public function getZaddList(string $tags, string $type, $options = null)
    {
        if (empty($tags) || empty($type)) {
            throw new \Exception('Get Zadd List Error');
        }
        
        return Redis::zrange($tags . '_'. $type, '0', self::ZADDLISTLIMIT, $options);
    }
    /**
     * 寫入緩存清單
     * @param string $tags 支付別名
     * @param string $type 組別
     * @param string $id
     * @return void
     */
    public function setListCache(string $tags, string $type, string $id)
    {
        if (empty($tags) || empty($type) || empty($id)) {
            throw new \Exception('Set List Cache Error');
        }

        Redis::rpush($tags . '_' . $type, $id);
    }
    /**
     * 寫入緩存資料
     * @param string $tags 支付別名
     * @param string $type 組別
     * @param string $id 辨識id
     * @param array $data
     * @return void
     */
    public function setTagsCache(string $tags, string $type, string $id, $data = array())
    {
        if (empty($tags) || empty($type) || empty($id)) {
            throw new \Exception('Set Tags Cache Error');
        }

        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forever($id, $data);
    }
    /**
     * 取得紀錄清單
     * @param string $tags 支付別名
     * @param string $type 組別
     * @return array
     */
    public function getListCache(string $tags, string $type)
    {
        if (empty($tags) || empty($type)) {
            throw new \Exception('Get List Cache Error');
        }

        return Redis::lrange($tags . '_' . $type, 0, self::SENDLISTLIMIT);
    }
    /**
     * 取得 cahce 儲存的值
     * @param string $tags 支付別名
     * @param string $type 組別
     * @param string $id 
     * @return array or string
     */
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
    public function deleteListValue(string $tags, string $type, string $value)
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
     * 內容已刪除, 但tag會殘留key
     */  
    public function deleteTagsValue(string $tags, string $type, string $value)
    {
        if (empty($tags) || empty($type) || empty($value)) {
            throw new \Exception('Delete Cache Value Error');
        }

        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forget($value);
    }
}