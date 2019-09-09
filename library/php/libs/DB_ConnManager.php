<?php
/**
 * Created by PhpStorm.
 * User: Yxs
 * Date: 2017/10/2
 * Time: 21:13
 */

namespace libs;


class DB_ConnManager
{
    private $mysql = null;
    private $redis = null;



    //静态变量保存全局实例
    private static $_instance = null;
    //私有克隆函数，防止外办克隆对象
    private function __clone() { }
    //私有构造函数，防止外界实例化对象
    private function __construct() { }

    //静态方法，单例统一访问入口
    static public function getInstance() {
        if(!self::$_instance instanceof self){
            self::$_instance = new self ();
        }
        return self::$_instance;
    }




    public function getMysqlConn(){
        if(!$this->mysql instanceof \libs\DB_PDO){
            $this->mysql = \libs\DB_PDO::getInstance();
        }
        return $this->mysql;
    }
    public function closeMysqlConn(){
        if($this->mysql instanceof \libs\DB_PDO){
            $this->mysql->close();
            $this->mysql = null;
        }
    }
    public  function  isMysqlConn(){
        if($this->mysql instanceof  \libs\DB_PDO)
            return true;
        else
            return false;
    }



    public function getRedisConn(){
        if(!$this->redis instanceof \libs\DB_Redis){
            $this->redis = \libs\DB_Redis::getInstance();
        }
        return $this->redis;
    }
    public function closeRedisConn(){
        if($this->redis instanceof \libs\DB_Redis){
            $this->redis->close();
            $this->redis = null;
        }
    }
    public  function  isRedisConn(){
        if($this->redis instanceof  \libs\DB_Redis)
            return true;
        else
            return false;
    }



}