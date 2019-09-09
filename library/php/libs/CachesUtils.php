<?php
/**
 * Created by PhpStorm.
 * User: yxs
 * Date: 2018-12-28
 * Time: 11:22 PM
 */

namespace libs;

/**
 * Trait CachesUtils
 * $this->redis 报错是因为这里没上下文，该trait要在一个BaseModel的class里。
 * @package libs
 */
trait CachesUtils{
    /**
     * 联合缓存
     * @param string $key 在redis中的缓存key
     * @param array $list 要获取的数据数组
     * @param int $keyTtl 全部或部分数据没命中缓存时则添加该缓存,且为他设置生命时间(s)
     * @param callable($listLack) $getDataFunc 全部或部分数据没命中缓存时,进行一个回掉方法,接收将要添加的缓存。
     * @return array
     */
    public function uniteCaches($key,$list,$keyTtl,$getDataFunc){
        $row = $this->redis->hmGet($key,$list);
        if(! count($listLack = array_keys($row,false))){
            return $row;
        }
        if(! ($rowNew = $getDataFunc($listLack)))
            return $rowNew;

        $this->setCaches($key,$rowNew,$keyTtl);
        return $rowNew + $row;
    }

    /**
     * 设置缓存
     * @param $key
     * @param $arr
     * @param $ttl
     */
    public function setCaches($key,$arr,$ttl){
        $this->redis->hMset($key, $arr);
        $this->redis->expire($key,$ttl);
    }

    /**
     * 更新缓存
     * @param $key
     * @param $arr
     * @param $ttl
     */
    public function updateCaches($key,$arr,$ttl){
        if($this->redis->EXISTS($key))
            $this->setCaches($key,$arr,$ttl);
    }
}