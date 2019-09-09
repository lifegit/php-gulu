<?php
namespace libs;

class Rand {
	const TYPE_NUM = "0123456789";
	const TYPE_ALL = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	
	
	/**
	 * 生成一个随机数
	 */
	function getRandChar($charType,$length){
		$strPol = $charType;
		$max = strlen($strPol)-1;
        $str='';
		for($i=0;$i<$length;$i++){
			$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		}
		return $str;
	}
	
}
