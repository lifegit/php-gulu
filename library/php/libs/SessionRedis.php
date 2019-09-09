<?php
/*
	date: 2017-12-22
	author : The Life
	version : 3.0
	README:以redis来实现共享session
    注:此class不会去使用 php.ini 相关session 的配置
demo:
    $session_redis = new SessionRedis();
    if($session_redis->init('127.0.0.1',6379,null,null,'token',900,0,true,'/','',false,true)){
        $session_redis->setValue("test",'testValue');
        var_dump($session_redis->getValue("test"));
        var_dump($session_redis->isExistKey("test"));
        var_dump($session_redis->delKey("test"));
        var_dump($session_redis->getValue("test"));
        var_dump($session_redis->getSessionName());
        var_dump($session_redis->getSessionId());
        var_dump($session_redis->get_surplus_gc_maxLifeTime());


        $session_redis->setTag('u1');
        var_dump($session_redis->getTag());

        $session_redis->update_gc_maxLifeTime();
        $session_redis->update_cookie_lifeTime();

        $session_redis->session_destroy();
    }else{
        echo 'not init!';
    }
*/
namespace libs;

use Exception;
use Redis;

class SessionRedis{
    /** @var \Redis */
    private $redis;
    /** @var string */
    private $sessionName;
    /** @var string */
    private $sessionId;
    /** @var string */
    private $formatSessionKey;
    /** @var int */
    private $gc_maxLifeTime;
    /** @var int */
    private $cookie_lifeTime;
    /** @var boolean */
    private $validateUA;
    /** @var string */
    private $path;
    /** @var string */
    private $domain;
    /** @var boolean */
    private $secure;
    /** @var boolean */
    private $httpOnly;
    /** @var string */
    private $tag;
    /** @var boolean*/
    private $isReady;


    /**
     * init 初始化
     * @param string    $host  	                                 redis_host
     * @param int       $port                                    redis_port
     * @param string    $password           default:NULL    	 redis_password
     * @param string    $dbIndex            default:NULL    	 redis_dbIndex
     * @param string    $sessionName        default:'token'      sessionName
     * @param int       $gc_maxLifeTime     default:900          session在redis中的存活时间(S),如果为-1则为永久(不建议)
     * @param int       $cookie_lifeTime    default:0            sessionID在客户端中Cookie储存的时间(S),默认是0,代表浏览器一关闭SessionID就作废,也就是会话级。如果非0，假设关闭浏览器并且再打开只要在此时间内,还会提交此SessionID
     * @param bool      $validateUA         default:true         验证客户端传过来的sessionId和redis对应的sessionID中的user-agent是否一致
     * @param string    $path               default:'/'          cookie的可用路径。如果设置为“/”，则Cookie将在整个内部可用。如果设置为 “/ foo /”，则cookie将只在/ foo /目录和所有子目录（如/ foo / bar / of）中 可用。
     * @param string    $domain             default:''           domain表示的是cookie所在的域，默认为请求的地址，如网址为www.jb51.net/test/test.aspx，那么domain默认为www.jb51.net。而跨域访问，如域A为t1.test.com，域B为t2.test.com，那么在域A生产一个令域A和域B都能访问的cookie就要将该cookie的domain设置为.test.com；如果要在域A生产一个令域A不能访问而域B能访问的cookie就要将该cookie的domain设置为t2.test.com
     * @param bool      $secure             default:false        指示仅通过 HTTPS 连接传输 cookie。这可以确保 cookie ID 是安全的，且仅用于使用 HTTPS 的网站。如果启用此功能，则 HTTP 上的会话 Cookie 将不再起作用。
     * @param bool      $httpOnly           default:false        当true时cookie只能通过HTTP协议访问时。且cookie不能被脚本语言（比如JavaScript）访问。有人认为，这种设置可以有效地帮助通过XSS攻击来减少身份盗用。在PHP 5.2.0中添加。
     * 详细配置文档说明见官方:  http://php.net/manual/en/function.setcookie.php
     * @return bool
     */
    public function init($host,$port,$password = null,$dbIndex = null,$sessionName = 'token',$gc_maxLifeTime = 900,$cookie_lifeTime = 0,$validateUA = true, $path = '/', $domain = '',$secure = false,$httpOnly = false) {
        $this->sessionName      = $sessionName;
        $this->gc_maxLifeTime   = $gc_maxLifeTime;
        $this->cookie_lifeTime  = $cookie_lifeTime;
        $this->validateUA       = $validateUA;
        $this->path             = $path;
        $this->domain           = $domain;
        $this->secure           = $secure;
        $this->httpOnly         = $httpOnly;
        if(($ret = $this->conn($host, $port, $password, $dbIndex)))
            $this->session_init();
        return $ret;
    }

    /**
     * 连接redis
     * @param $host
     * @param $port
     * @param null $password
     * @param null $dbIndex
     * @return bool
     */
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
                if($dbIndex != null && is_numeric($dbIndex)){
                	$redis->select($dbIndex);
                }
                   
                $this->redis = $redis;
                return true;
            }
            return FALSE;
        }
        catch(Exception $e){
            return FALSE;
        }
    }

    /**
     * 初始化 session_ready
     * 判断是否存在sessionID且验证,并将状态修改到isReady
     */
    private function session_init(){
        if(isset($_COOKIE[$this->sessionName])){
            //$_COOKIE 中存在
            $this->sessionId = $_COOKIE[$this->sessionName];
        }else if(isset($_REQUEST[$this->sessionName])){
            //post/get 参中存在
            $this->sessionId = $_REQUEST[$this->sessionName];
        }

        if($this->sessionId == null)
            $this->isReady = false;
        else{
            //存在情况下进行验证
            $this->formatSessionKey();

            if($this->validateUA){
                $this->isReady = md5($_SERVER['HTTP_USER_AGENT']) != $this->redis->hGet($this->formatSessionKey,'UA') ? false : true;
            }else{
                $this->isReady = $this->redis->exists($this->formatSessionKey) === true ? true : false;
            }
        }
    }
    /**
     * ready 判断存在状态
     * @param bool $isStart 不存在是否是否初始化
     * @return bool
     */
    private function session_ready($isStart){
        if($this->isReady == true)
            return true;
        if(!$isStart)
            return false;
        $this->session_start();
        $this->isReady = true;
        return true;
    }


    /**
     * 开始一个新会话
     */
    private function session_start(){
        //生成一个sessionId,且判断是否存在
        do{
            $this->sessionId = $this->getRandChar();
            $this->formatSessionKey();
        }while($this->redis->exists($this->formatSessionKey) === true);
        //设置UA或临时变量
        $this->validateUA ? $this->redis->hSet($this->formatSessionKey,'UA',md5($_SERVER['HTTP_USER_AGENT'])) : $this->redis->hSet($this->formatSessionKey,'t',null);
        //设置过期时间
        if($this->gc_maxLifeTime >= 1)
            $this->redis->expire($this->formatSessionKey,$this->gc_maxLifeTime);
        //写出sessionId到cookie
        $this->setcookie($this->sessionName,$this->sessionId);
    }

    /**
     * 把sessionId进行格式化
     */
    private function formatSessionKey(){
        $this->formatSessionKey = "SE:".$this->sessionName.':'.$this->sessionId;
    }

    /**
     * 设置tag,设置后相同的tag的sessionId会被强行删除,这样可以保证当多终端时上线时,会强制将旧终端信息销毁,保证只有一个终端在线
     * @param string $tag
     * @return bool
     */
    public function setTag($tag){
        if($this->session_ready(true) === false)
            return false;
        $tag_format = "SE:TAG:$tag";
        if(($sessionKey = $this->redis->get($tag_format)) !== false && $sessionKey != $this->formatSessionKey){
            $this->redis->del($sessionKey);//发生tag冲突则删除旧终端的信息
        }
        $this->gc_maxLifeTime >= 1 ? $this->redis->set($tag_format,$this->formatSessionKey,$this->gc_maxLifeTime):$this->redis->set($tag_format,$this->formatSessionKey);//为tag设置时间
        $this->redis->hSet($this->formatSessionKey,'t',$tag);
        $this->tag = $tag;
        return true;
    }

    /**
     * 删除tag
     * @return bool
     */
    public function delTag(){
        if($this->session_ready(false) === false)
            return false;
        $tag = $this->getTag();
        if($tag != null){
            $tag_format = "SE:TAG:$tag";
            $this->redis->del($tag_format);
        }
        return true;
    }
    /**
     * 获取tag
     * @return null|string
     */
    public function getTag(){
        if($this->session_ready(false) === false)
            return null;
        if($this->tag != null)
            return $this->tag;
        $tag = $this->redis->hGet($this->formatSessionKey,'t');
        if($tag == '')
            return null;

        $this->tag = $tag;
        return $tag;
    }

    /**
     * 刷新gc_maxLifeTime(sessionId在redis)的时间(有tag也会刷新tag时间)
     * @return bool
     */
    public function update_gc_maxLifeTime(){
        if($this->session_ready(false) === false)
            return false;
        $ret = $this->gc_maxLifeTime == -1 ? $this->redis->persist($this->formatSessionKey) : $this->redis->expire($this->formatSessionKey,$this->gc_maxLifeTime);
        if($ret === true){
            $tag = $this->redis->hGet($this->formatSessionKey,'t');
            if($tag != '')
                $this->gc_maxLifeTime == -1 ? $this->redis->persist($tag) : $this->redis->expire($tag,$this->gc_maxLifeTime);
        }
        return true;
    }
    /**
     * 获取剩余的gc_maxLifeTime(sessionId在redis)时间
     * @return int (单位:秒)
     */
    public function get_surplus_gc_maxLifeTime(){
        if($this->session_ready(false) === false)
            return -1;
        return $this->redis->ttl($this->formatSessionKey);
    }

    /**
     * 刷新cookie_lifeTime(sessionId在客户端cookies中)的时间
     */
    public function update_cookie_lifeTime(){
        $this->setcookie($this->sessionName,$this->sessionId);
    }

    /**
     * 添加或修改一个key-Value
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function setValue($key,$value){
        if($this->session_ready(true) === false)
            return false;
        $this->redis->hSet($this->formatSessionKey,$key,$value);
        return true;
    }

    /**
     * 获取一个key中的value,如果没有此key,返回null
     * 请您一定判null,万一因客观原因导致redis丢失数据,此时获取只能返回null
     * @param string $key
     * @return null|string
     */
	Public function getValue($key){
        if($this->session_ready(false) === false)
            return null;
	    $ret = $this->redis->hGet($this->formatSessionKey,$key);
        return $ret === false ? null : $ret;
    }

    /**
     * 判断key是否存在
     * @param string $key
     * @return bool
     */
	Public function isExistKey($key){
        if($this->session_ready(false) === false)
            return false;
        return $this->redis->hExists($this->formatSessionKey,$key);
    }

    /**
     * 删除一个key
     * @param string $key
     * @return bool
     */
	Public function delKey($key){
        if($this->session_ready(false) === false)
            return false;
	    return $this->redis->hDel($this->formatSessionKey,$key) === 1 ? true : false;
    }

    /**
     * 删除所有的变量,但不结束当前的session会话
     * @return bool
     */
	Public function session_unset(){
        if($this->session_ready(false) === false)
            return false;
        $this->delTag();
        return $this->redis->del($this->formatSessionKey) === 1 ? true : false;
    }

    /**
     * 结束当前的session会话,但不清空此会话redis中的任何变量
     */
    Public function session_finish(){
        $this->setCookie($this->sessionName,null);
    }

    /**
     * 结束当前的session会话,并清空此会话redis中的所有变量,不会影响其他会话。
     */
    Public function session_destroy(){
        $this->session_finish();
        $this->session_unset();
    }
    /**
     * 获取当前会话名称
     * @return string
     */
	Public function getSessionName(){
        return $this->sessionName;
    }

    /**
     * 设置sessionName
     * @param string $sessionName
     */
    public function setSessionName($sessionName){
        $this->sessionName = $sessionName;
    }

    /**
     * 设置sessionId
     * 注:如果通过此方法设置sessionId,将不会进行validateUA判断,即便构造此对象时选择了true
     * @param string $sessionId
     */
    public function setSessionId($sessionId){
        $this->sessionId = $sessionId;
        $this->formatSessionKey();
        $this->isReady = $this->redis->exists($this->formatSessionKey) ? true : false;
    }
    /**
     * 获取当前会话标识号
     * @return string
     */
    public function getSessionId(){
        return $this->sessionId;
    }

    /**
     * 生成一个26位随机数
     */
    private function getRandChar(){
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        $str='';
        for($i=0;$i<26;$i++){
            $str.=$strPol[rand(0,$max)];
        }
        return $str;
    }
    /**
     * 为 header 添加一个 Set-Cookie
     * @param string $sessionName
     * @param string $sessionId
     */
    private function setCookie($sessionName,$sessionId){
        //Set-Cookie: =[; =][; expires=][; domain=][; path=][; secure][; HttpOnly]
        $domain = $this->domain!='' ? '; domain=' . $this->domain : '';
        $path = '; path=' . $this->path;
        $secure = $this->secure ? '; secure' : '';
        $httpOnly = $this->httpOnly ? '; HttpOnly' : '';
        if ($sessionId == null) {
            //删除
            header("Set-Cookie:{$sessionName}=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0{$domain}{$path}{$secure}{$httpOnly}", false);
        } else {
            //添加或修改
            $MaxAge = $this->cookie_lifeTime == 0 ? '' : '; Max-Age=' . $this->cookie_lifeTime;
            header("Set-Cookie:{$sessionName}={$sessionId}{$MaxAge}{$domain}{$path}{$secure}{$httpOnly}", false);
        }
    }
}