<?php
namespace libs;
/**
 * RC4 加密解密类
 * 
 *author:杨旭森
 *version:1.0
 *time:2017-01-15
 * 
 * 使用方法:
 $rc4 = new rc4Code();
 echo $rc4-> encrypt ('2');//加密
 echo $rc4-> decrypt ('1c');//解密
 * 
 */
class Rc4Code{
    private $encrypt_key = null;
    private $decrypt_key = null;

	/**
	 * 构造方法
	 * $encrypt_key 加密的key
	 * $decrypt_key 解密的key
	 */
	public function __construct($encrypt_key,$decrypt_key){
		$this->encrypt_key=$encrypt_key;
        $this->decrypt_key=$decrypt_key;
	}
	
	//加密
	Public function encrypt($string){		
		return @bin2hex($this->rc4($this->encrypt_key,$string));
	}
	
	//解密
	Public function decrypt($string){
		return @$this->rc4($this->decrypt_key,hex2bin($string));
	}
	
	private function rc4($key , $data){
		$keyLength = strlen($key);
		$S = array();
		for($i = 0; $i < 256; $i++) $S[$i] = $i;
		$j = 0;
		for ($i = 0; $i < 256; $i++)
		{
		$j = ($j + $S[$i] + ord($key[$i % $keyLength])) % 256;
		$this->swap($S[$i], $S[$j]);
		}
		
		$dataLength = strlen($data);
		$output ="";
		 for ($a = $j = $i = 0; $i < $dataLength; $i++)
		{
		 $a = ($a + 1) % 256;
		 $j = ($j + $S[$a]) % 256;
		 $this->swap($S[$a], $S[$j]);
		 $k = $S[(($S[$a] + $S[$j]) % 256)];
		 $output .= chr(ord($data[$i]) ^ $k);
		}
		
		return $output;
	}

	private function swap(&$a, &$b){
		$tmp = $a;
		$a = $b;
		$b = $tmp;
	}
}