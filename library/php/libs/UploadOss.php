<?php
/**
 * author:杨旭森
 * version：1.0
 * time:v1: 2018-11-18
 * */
namespace libs;
//demo:
//	$upfiles = new UploadOss(array('*'),'upload',3145728,true,null);//230Kb
//	var_dump($upfiles->upload_file());

use configs\System;
use OSS\Core\OssException;
use OSS\OssClient;

class UploadOss{
	//可编辑参数:
	//允许的上传文件类型   array()或array('*') 为允许所有类型
	private $allowType = array('*');
	//文件被上传到的目标目录,如果空则默认当前目录,不为空则会自动建立文件夹(Linux 请打开777权限)	
	private $dir = 'upload';
	//允许上传文件的最大值
	private $maxSize = 3145728;//字节 默认3M 请不用3*1024*1024 这种方式
	//file ID
	private $fileID = 'file';


    /**
     * UploadOss constructor.
     * @param null $allowType   {Array}     允许的上传文件类型
     * @param null $dir         {String}	文件被上传到的目标目录
     * @param null $maxSize     {int}		允许上传文件的最大值
     * @param null $fileID      {String}	客户端传过来的文件ID
     */
	//构造函数
	//具体数据类型请查看class的属性
	public function __construct($allowType = null,$dir = null,$maxSize = null,$fileID = null){
	    if($allowType !== null)
			$this->allowType = $allowType;
		if($dir !== null)
			$this->dir = $dir;
		if($maxSize !== null)
			$this->maxSize = $maxSize;
		if($fileID !== null)
			$this->fileID = $fileID;
	}
	/**
	 * 文件保存
	 * return :
	 * 保存成功:array ('return'=>true,'finalDir'=>{String},'finalFileName'=>{String},'finalDirFileName'=>{String});
	 * 保存失败:array ('return'=>false,'info'=>{String},'errorCode'=>{String});	//所有错误信息请查getErrorInfo()函数
	 */
	public function upload_file(){
		$fileID=$this->fileID;

		if(empty($_FILES) || empty($_FILES[$fileID]["name"]) || empty($_FILES[$fileID]["tmp_name"]) || empty($_FILES[$fileID]["size"]) || $_FILES[$fileID]["size"] <= 0){
			return $this->getErrorInfo(-1);
		}
		
		if($_FILES[$fileID]["error"] != UPLOAD_ERR_OK){
			return $this->getErrorInfo($_FILES[$fileID]["error"]);
		}

		//取得上传文件名+文件类型
		$name = $_FILES[$fileID]["name"];
		
		//取得文件名
		$filename = $this->getPathInfo($name,'filename');
		$filename=iconv('utf-8','gbk',$filename);
		if($filename === null){
			return $this->getErrorInfo(-2);
		}	

		//判断文件类型
		$filetype = $this->getPathInfo($name,'extension');
		if($filetype === null ||  !(count($this->allowType)==0 || $this->allowType[0]=='*' || in_array($filetype,$this->allowType) ) ){
			return $this->getErrorInfo(-3);
		}
		
		//判断文件大小
		$size=$_FILES[$fileID]["size"];
		if($size > $this->maxSize){
			return $this->getErrorInfo(-4);
		}

        $finalDir = $this->dir.'/';
        $rand = new Rand();
        require_once __DIR__ . '/aliyun-oss-php-sdk-2.3.0.phar';


        try {
            $ossClient = new OssClient(System::ALIYUN_ACCESSKEYID, System::ALIYUN_ACCESSKEYSECRET, System::ALIYUN_OSS_ENDPOINT);

            //判断是否有重复文件名,有则重新获取随机码
            do{
                //$finalFileName = $filename . '_' . rand(1000,9999) . '.' . $filetype;
                $finalFileName = time() . '_' . $rand->getRandChar(Rand::TYPE_ALL,8) . '.' . $filetype;
                $finalDirFileName = $finalDir . $finalFileName;
            }while( $ossClient->doesObjectExist(System::ALIYUN_OSS_BUCKET,$finalDirFileName) );

            $ossClient->putObject(System::ALIYUN_OSS_BUCKET, $finalDirFileName, file_get_contents($_FILES[$fileID]["tmp_name"]));


            return array ('return'=>true,'finalDir'=>$finalDir,'finalFileName'=>$finalFileName,'finalHOst'=>System::ALIYUN_OSS_DOMAIN,'finalDirFileName'=>$finalDirFileName);
        } catch (OssException $e) {
            return $this->getErrorInfo(0,$e->getMessage());
        }
	}

    /**
     * 获取文件信息
     * @param $filename string 要获取文件名的文件
     * @param $type string 类型
     * @return null
     */
	private function getPathInfo($filename,$type){
   		$info = pathinfo($filename);
		if(empty($info[$type]) || $info[$type]=='')
			return null;
		else
			return $info[$type];
   }

    /**
     * 获取错误信息
     * @param $errorCode integer
     * @param $errorStr  string 当code为0时,自定义的错误内容
     * @return array
     */
	private function getErrorInfo($errorCode = 0,$errorStr = '未知错误'){
		//系统错误
		if ($errorCode == UPLOAD_ERR_INI_SIZE)//1
			return array ('return'=>false,'info'=>'上传文件大小超过服务器允许上传的最大值:php.ini中设置upload_max_filesize选项限制的值','errorCode'=>$errorCode);
		else if ($errorCode == UPLOAD_ERR_FORM_SIZE)//2
			return array ('return'=>false,'info'=>'上传的文件大小超出浏览器限制:超过HTML表单中隐藏域MAX_FILE_SIZE选项指定的值','errorCode'=>$errorCode);
		else if ($errorCode == UPLOAD_ERR_PARTIAL)//3
			return array ('return'=>false,'info'=>'文件只有部分被上传','errorCode'=>$errorCode);
		else if ($errorCode == UPLOAD_ERR_NO_FILE)//4
			return array ('return'=>false,'info'=>'没有文件被上传','errorCode'=>$errorCode);
		else if ($errorCode == UPLOAD_ERR_NO_TMP_DIR)//6
			return array ('return'=>false,'info'=>'找不到临时文件夹','errorCode'=>$errorCode);
		else if ($errorCode == UPLOAD_ERR_CANT_WRITE)//7
			return array ('return'=>false,'info'=>'文件写入失败','errorCode'=>$errorCode);
		else if ($errorCode == UPLOAD_ERR_EXTENSION)//8
			return array ('return'=>false,'info'=>'php文件上传扩展没有打开','errorCode'=>$errorCode);
		//自定义错误
		else if ($errorCode == 0)
			return array ('return'=>false,'info'=>$errorStr,'errorCode'=>$errorCode);
		else if ($errorCode == -1)
			return array ('return'=>false,'info'=>'未上传文件','errorCode'=>$errorCode);
		else if ($errorCode == -2)
			return array ('return'=>false,'info'=>'没有文件名','errorCode'=>$errorCode);
		else if ($errorCode == -3)
			return array ('return'=>false,'info'=>'不支持此文件类型','errorCode'=>$errorCode);
		else if ($errorCode == -4)
			return array ('return'=>false,'info'=>'文件超过了设定的阈值','errorCode'=>$errorCode);
		else
			return array ('return'=>false,'info'=>'系统错误','errorCode'=>null);													
   }
}