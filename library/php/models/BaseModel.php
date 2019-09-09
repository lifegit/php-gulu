<?php
namespace models;

/**
 * Created by PhpStorm.
 * User: Yxs
 * Date: 2017/9/27
 * Time: 21:47
 */

/**
 * 一个基类,为所有model提供mysql与redis链接
 * Class BaseModel
 * @package model
 */
class BaseModel{
    /** @var \Redis */
//    protected $redis;
    /** @var \libs\DB_PDO */
//    protected $mysql;


    public function  __get($name){
        if($name === 'mysql'){
            return  \libs\DB_ConnManager::getInstance()->getMysqlConn();
        }
        else if ($name === 'redis'){
            $redis =  \libs\DB_ConnManager::getInstance()->getRedisConn();
            return $this->redis = $redis->getRedis();
        }
//          else if ($name === 'mysql_cache'){
//            $this->mysql_cache =  \libs\DB_ConnManager::getInstance()->getMysqlCacheConn();
//        }
    }



}
