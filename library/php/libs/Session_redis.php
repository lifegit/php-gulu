<?php
/*
	date: 2016-11-24
	author : 杨旭森
	version : 2.0
	README:以redis来实现共享session
*/
namespace libs;


use configs\System;


class Session_redis{


    public function conn($session_name){
        $this->init(System::REDIS_SESSION_HOST,System::REDIS_SESSION_PORT,System::REDIS_SESSION_PASSWORD,$session_name,false);

    }

	/*初始化
        *ip 			redis_ip        默认见私有参
        *port			redis_port	    默认见私有参
        *password		redis_password	默认见私有参
        *session_name	session_name	默认php.ini的session_name。
        *only_cookies	(true or false);是否当客户端禁用cookies时,启用URL来传输SessionID;默认false。  false启动url true禁止url
	*/
    private function init($ip,$port,$password,$session_name,$only_cookies = false){
        $path="tcp://$ip:$port"; // tcp://127.0.0.1:6379
        if($password!='')
            $path=$path.'?auth='.$password;//tcp://127.0.0.1:6379?auth=password 带密码的redis

        ini_set('session.save_handler', 'redis');//使用redis做session
        ini_set('session.save_path', $path);//设置redis连接信息


        if($only_cookies==false){
            ini_set('session.use_only_cookies', '0');//关闭仅使用cookies存放SessionID
            ini_set('session.use_trans_sid', '1');//允许SessionID通过URL明文传输
        }else{
            ini_set('session.use_only_cookies', '1');//打开仅使用cookies存放SessionID
            ini_set('session.use_trans_sid', '0');//禁止SessionID通过URL明文传输
        }
        if($session_name!='')
            ini_set('session.name', $session_name);
        session_start();//开始一个会话或者返回已经存在的会话
    }

	/**
	*时间设置
	*注:如果实例化本对象并没有调用此方法,将使用php.ini中的设置
	*参数如下:
	*gc_maxlifetime		Session在redis中的存活时间(S) 默认15分钟
	*cookie_lifetime	SessionID在客户端Cookie储存的时间(S),默认是0,代表浏览器一关闭SessionID就作废。如果非0，假设关闭浏览器并且再打开只要在此时间内,还会提交此SessionID
	*/
	Public function setTime($gc_maxlifetime=900,$cookie_lifetime=0){
		ini_set('session.gc_maxlifetime', $gc_maxlifetime);//Session在redis中的存活时间(S)	
		ini_set('session.cookie_lifetime', $cookie_lifetime);//SessionID在客户端Cookie储存的时间(S)
    }
	
	/**
	*添加或修改一个key-Value
	*/
	public function setValue($key,$value){
		$_SESSION[$key]=$value;//boolean session_register(string name); 在5.4中已被遗弃(5.3警告),因兼容性,故用$_SESSION[$key]=$value
	}
	
	
	/*
	*获取一个key中的value
	*如果没有此key,返回null
	*/
	Public function getValue($key){
		if(isset($_SESSION[$key]))	
			return $_SESSION[$key];
		else
			return null;
    }
	
	/*
	*删除一个key
	*成功返回 true  ; 失败返回 false
	*/
	Public function delkey($key){
		unset($_SESSION[$key]);//boolean session_session_unregister(string name);在5.4中已被遗弃(5.3警告),因兼容性,故用unset();
    }	
	
	/*
	*判断key是否存在
	*存在返回 true ; 不存在返回 false
	*/
	Public function isExist_key($key){//boobean session_is_registered(string name);在5.4中已被遗弃(5.3警告),因兼容性,故用isset($_SESSION[$key]);
		if(isset($_SESSION[$key]))	
			return true;
		else
			return false;
    }
	
	
	/*
	*获取当前会话标识号
	*返回 字符串
	*/
	Public function getSession_id(){
		return session_id();
    }
	/*
	*设置当前会话标识号
	*/
	Public function setSession_id($id){
		session_id($id);
    }
	
	/*
	*获取当前会话名称
	*返回 字符串
	*/
	Public function getSession_name(){
		return session_name();
    }
	/*
	*设置当前会话名称
	*/
	Public function setSession_name($name){
		session_name($name);
    }


	/*
	*信息编码
	*返回 字符串
	*/
	Public function Session_encode(){
		return session_encode();
    }
	/*
	*信息解码
	* 成功返回true 失败返回  false
	*/
	Public function Session_decode($data){
		return Session_decode($data);
    }
		
	
	
	/**
	*删除所有已注册的变量,并且不结束会话
	*return = true or false
	*/
	Public function session_unset(){
		return session_unset();
    }
	
	/*
	*结束当前的sessionid会话,并清空此会话中的所有变量,不会影响其他会话。
	*return = ture
	*/
	Public function session_destroy(){
		return session_destroy();
    }
	
}