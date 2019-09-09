<?php
namespace libs;

class Ip{
	/**
	 * 获取客户端IP
	 */
	public	function getClientIP() {
		if (getenv('HTTP_CLIENT_IP')) {
			$ip = getenv('HTTP_CLIENT_IP');
		}
		else if (getenv('HTTP_X_FORWARDED_FOR')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		else if (getenv('HTTP_X_FORWARDED')) {
			$ip = getenv('HTTP_X_FORWARDED');
		}
		else if (getenv('HTTP_FORWARDED_FOR')) {
			$ip = getenv('HTTP_FORWARDED_FOR');
		}
		else if (getenv('HTTP_FORWARDED')) {
			$ip = getenv('HTTP_FORWARDED');
		}
		else {
		$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}



    /**
     * 获取顶级域名
     */
    public function getTopDomainName(){
        $stringUtils = new \libs\StringUtils();
        $topDomainName = $stringUtils->strSub($_SERVER['SERVER_NAME'] . '/','.','/');
        return $topDomainName === '' ? $_SERVER['SERVER_NAME'] : $topDomainName;
    }

}