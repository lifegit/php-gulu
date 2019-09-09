<?php
namespace libs;

class StringUtils{

    static function bool($str){
        return $str === 'true' ? true : false;
    }

    /**
     * 手机号遮码化
     * @param $mobile
     * @param int $front
     * @param int $behind
     * @param string $secret
     * @return string
     */
    function mobilePrivacy($mobile,$front = 3, $behind = 4, $secret = '*'){
        if(strlen($mobile) >= 11){
            $code = '';
            for ($i = $front + $behind; $i < 11; $i++){
                $code .= $secret;
            }
            return substr($mobile,0,$front) . $code . substr($mobile,-$behind,$behind);
        }
        return $mobile;
    }

	/** 文本_取出中间文本
	 * @param $str 待取的字符串
	 * @param $start 前面的字符串,如果传null则为从头开始
	 * @param $end  后面的字符串,如果传null则取到尾
	 * @return string   取出的字符串
	 */
	function strSub($str, $start, $end) {
	    //取出前面位置,如果传null,直接位置为0,否则找前面位置
	    if($start === null)
	        $start_position = 0;
	    else{
	        $start_position = strpos($str,$start);
	        //如果找不到前面位置 直接返回空
	        if($start_position === false)
	            return '';
	        $start_position = $start_position + strlen($start);
	    }
	    //取出后面位置,如果传null,直接位置为最后,否则找后面位置
	    if($end === null)
	        $end_position = strlen($str);
	    else{
	        $end_position = strpos($str,$end,$start_position);
	        //如果找不到后面位置 直接返回空
	        if($end_position === false)
	            return '';
	    }
	    //取出中间内容
	    return substr($str,$start_position,$end_position-$start_position);
	}	
}
