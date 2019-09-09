<?php
/**
 * Created by PhpStorm.
 * User: yxs
 * Date: 2019-1-23
 * Time: 5:11 PM
 */

namespace libs;

use configs\System;
use dies;
use libs\SessionRedis;
use function PHPSTORM_META\type;

class SessionManager
{
    /** @var SessionRedis */
    private $session_redis = null;

    // keys
    public $KEY_ID = 'id';
    public $KEY_USERNAME = 'username';

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
        if(!$this->session_redis->init(System::REDIS_SESSION_HOST,System::REDIS_SESSION_PORT,System::REDIS_SESSION_PASSWORD,System::REDIS_SESSION_DBINDEX,'token_cyss19_headquarters',1800,0,true,'/','',false,false)){
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

    public function login($id,$username){
        $this->session_redis->setValue($this->KEY_ID,$id);
        $this->session_redis->setValue($this->KEY_USERNAME,$username);
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

    /**
     * 是否就绪
     * @param null $authorityKey null 不会验证权限
     * @param null $authorityType null 不会验证权限
     * @return int|null|string
     */
    public function isReady($authorityKey = null,$authorityType = null){
        // check-login
        if(! $this->isLogin(($headquartersId = $this->getID())))
            dies::die($this->notLogin());

        // check-authority
        if($authorityKey && $authorityType){
            $res = (new HeadquartersAuthorityModel())->getInfo($headquartersId,[$authorityKey]);
            if(! \Create::isSuccess($res))
                dies::die($res);

            if(!stristr($res[HeadquartersAuthorityModel::RESULT_AUTHORITY][$authorityKey],$authorityType))
                dies::die($this->notAuthority());
        }

        return $headquartersId;
    }

    public function getID(){
        // return 1;
        $id = $this->session_redis->getValue($this->KEY_ID);
        if($id !=null && $id !='')
            return $id;
        else
            return null;
    }
    public function getUsername(){
        $id = $this->session_redis->getValue($this->KEY_USERNAME);
        if($id !=null && $id !='')
            return $id;
        else
            return null;
    }

    public function setSurvival(){
        $this->session_redis->update_gc_maxLifeTime();
    }

    public function notLogin(){
        header("HTTP/1.1 901 notLogin");
        return \Create::error('未登录');
    }

    public function notAuthority(){
        // header("HTTP/1.1 902 notAuthority");
        return \Create::error('无权限');
    }
}