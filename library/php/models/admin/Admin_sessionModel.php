<?php
namespace models\admin;

use configs\System;
use libs\SessionRedis;

class Admin_sessionModel{
    /** @var SessionRedis */
    private $session_redis = null;

    // keys
    const KEY_ID = 'id';
    const KEY_USERNAME = 'username';

    //静态变量保存全局实例
    private static $_instance = null;
    //私有克隆函数，防止外办克隆对象
    private function __clone() { }
    //私有构造函数，防止外界实例化对象
    private function __construct() {
        $this->init();
    }

    private function init(){
        $this->session_redis = new SessionRedis();
        if(!$this->session_redis->init(System::REDIS_SESSION_HOST,System::REDIS_SESSION_PORT,System::REDIS_SESSION_PASSWORD,System::REDIS_SESSION_DBINDEX,'token_task_admin',1800,0,true,'/','',false,false)){
            if (System::$CONN_ERROR_EXIT)
                die(System::$CONN_ERROR_INFO);
            else
                $this->init();
        }
    }

    //静态方法，单例统一访问入口
    static public function getInstance() {
      if(!self::$_instance instanceof self){
          self::$_instance = new self ();
      }
      return self::$_instance;
    }

    public function setSessionId($sessionId){
        $this->session_redis->setSessionId($sessionId);
    }
    public function getSurplusLifeTime(){
        return $this->session_redis->get_surplus_gc_maxLifeTime();
    }

	public function login($id){
        $this->session_redis->setValue(self::KEY_ID,$id);
//        $this->session_redis->setValue(self::KEY_USERNAME,$username);
		return TRUE;
	}
	public function loginOut(){
        $this->session_redis->session_destroy();
		return TRUE;
	}

    public function isLogin($id = 0){
        if($id === 0)
            $id = $this->getID();
        return $id != null ? true : false;
    }

	public function getID(){
//        return 1;
		$id = $this->session_redis->getValue(self::KEY_ID);
		if($id !=null && $id !='')
			return $id;
		else
			return null;
	}
//    public function getUsername(){
////        $id = $this->session_redis->getValue(self::KEY_USERNAME);
////        if($id !=null && $id !='')
////            return $id;
////        else
////            return null;
////    }


    public function setSurvival(){
        $this->session_redis->update_gc_maxLifeTime();
    }

    public function getTokenKey(){
        return \Create::success(['token' => [$this->session_redis->getSessionName() => $this->session_redis->getSessionId()]]);
    }

    public function notLogin(){
        header("HTTP/1.1 901 notLogin");
        return \Create::error('未登录');
    }

}
