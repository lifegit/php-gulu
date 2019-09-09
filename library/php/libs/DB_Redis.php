<?php
/**
 *author:杨旭森
 *version:2.0
 *time:2016-12-26
 */
namespace libs;


use Exception;
use Redis;

class DB_Redis{
    /** @var \Redis */
	private $redis = null;
    //静态变量保存全局实例
    private static $_instance = null;
    //私有克隆函数，防止外办克隆对象
    private function __clone() { }
    //静态方法，单例统一访问入口
    static public function getInstance() {
        if(!self::$_instance instanceof self){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    //私有构造函数，防止外界实例化对象
    private function __construct() {
        $this->init();
    }


    private function init(){
        //连接
        if(!$this->conn(\configs\System::REDIS_HOST,\configs\System::REDIS_PORT,\configs\System::REDIS_PASSWORD,\configs\System::REDIS_DBINDEX)) {
            if (\configs\System::$CONN_ERROR_EXIT)
                die(\configs\System::$CONN_ERROR_INFO);
            else
                $this->init();
        }
    }




    private function conn($host,$port,$password = null,$dbIndex = null){
        try
        {
            $redis = new Redis();
            //使用 pconnect 会对高并发下会有利,pconnect 会将链接的状态保存在php-fpm内进行资源复用,即使本次web访问结束或close()断开链接时只会将本对象的链接释放,而不会对php-fpm内的链接释放,下次web访问的时候,会自动复用相同host与port的链接。如果对php-fpm进行stop或reload时,会释放所有的redis链接。
            $res = $redis->pconnect($host,$port,4);
            //$res = $redis->open($host,$port,3);
            if($res == true){
                if($password != null && $password != '')
                    $redis->auth($password);
                if($dbIndex != null && is_numeric($dbIndex))
                    $redis->select($dbIndex);
				$this->redis = $redis;
				return true;
            }
            return FALSE;
        }
        catch(Exception $e){
            return FALSE;
        }
    }
	public function getRedis(){
		return $this->redis;
	}
	public function isPing(){
		try
		{		
			@$res = $this->redis->ping();
			if($res != '+PONG'){
				throw new Exception("The Redis is disconnected"); 
			}
			return TRUE;
		}
		catch(Exception $e){
			return FALSE;
		}		
	}


    /**
     * 断开连接
     */
    public	function close(){
        self::$_instance = null;
        if($this->redis != null)
            $this->redis->close();
        $this->redis = null;
    }
    public	function __destruct(){
        $this->close();
    }
}