<?php
namespace libs;

class WrapModel{
	/**
	 * 换行替换
	 * 将\n \r\n 替换为<br/>
	 */
	function wrapToBr($str){
		$str=str_replace("\n","<br/>",$str);
		$str=str_replace("\r","<br/>",$str);
		$str=str_replace("\r\n","<br/>",$str); 
		return $str;
	}
	/**
	 * 换行替换
	 * 将<br/>替换为\r\n 
	 */
	function brTowrap($str){
		$str=str_replace("<br/>","\r\n",$str); 
		return $str;
	}	
}
