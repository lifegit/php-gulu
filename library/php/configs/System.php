<?php
namespace configs;

/**
 * Class System
 * 系统配置
 */
class System{
    // mysql 业务
    const  PDO_DSN      		     = '127.0.0.1:3306';
    const  PDO_USERNAME 		     = '';
    const  PDO_PASSWORD 		     = '';
    const  PDO_DBNAME   		     = '';


    // redis 缓存
    const  REDIS_HOST			     = '127.0.0.1';//123.206.222.177
    const  REDIS_PORT			     = 6379;
    const  REDIS_PASSWORD  		     = null;
    const  REDIS_DBINDEX		     = 2;

    // redis SESSION 缓存
    const  REDIS_SESSION_HOST	     = '127.0.0.1';
    const  REDIS_SESSION_PORT		 = 6379;
    const  REDIS_SESSION_PASSWORD  	 = null;
    const  REDIS_SESSION_DBINDEX     = 1;

    // 阿里云 key
    const ALIYUN_ACCESSKEYID        = '';
    const ALIYUN_ACCESSKEYSECRET    = '';

    // 阿里云 oss
    const ALIYUN_OSS_ENDPOINT       = '';
    const ALIYUN_OSS_BUCKET         = '';
    const ALIYUN_OSS_DOMAIN         = '';


    //连接不上的预处理结果
    static $CONN_ERROR_EXIT 		 = true;//无论redis还是mysql如果连接失败则,如果为true不再连接,直接shwo出$CONN_ERROR_INFO后退出[用在web];如果为false,继续进行链接,直到连接成功[用在service]
    static $CONN_ERROR_INFO 		 = '{"return":false,"info":"\u7cfb\u7edf\u9519\u8bef"}';
}
