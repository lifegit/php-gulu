<?php
/**
 * Created by PhpStorm.
 * User: Yxs
 * Date: 2017/12/31
 * Time: 2:49
 */

namespace libs;


use configs\System;
use OSS\Core\OssException;
use OSS\OssClient;

class FileManager{

    const KEY_URL = 'url';
    const KEY_FILE = 'file';

    const RESULT_FILE = 'file';


    public function fileUpload_user($uid){
        return $this->upload(array('jpeg','jpg','bmp','png','gif'),"user/$uid",3145728);//3M
    }

    public function fileUpload_agent_avatar($type){
        return $this->upload(array('jpeg','jpg','bmp','png','gif'),"/avatar/$type",2097152);//2M
    }

    public function fileDel($fileName){
//        //file
//        return unlink($fileName) ? \Create::success() : \Create::error('删除失败');

        //oss
        try {
            $ossClient = new OssClient(System::ALIYUN_ACCESSKEYID, System::ALIYUN_ACCESSKEYSECRET, System::ALIYUN_OSS_ENDPOINT);
            $ossClient->deleteObject(System::ALIYUN_OSS_BUCKET,$fileName);
            return \Create::success();
        } catch (OssException $e) {
            return \Create::error('删除失败');
        }
    }

    /**
     * 基_上传
     * @param $type_arr
     * @param $fileDir
     * @param $maxSize
     * @return array
     */
    private function upload($type_arr,$fileDir,$maxSize){
//        // file
//        $upload = new \libs\Upload($type_arr,__DIR__ .'/../../../file/'.$fileDir,$maxSize,false);
//        $ret = $upload->upload_file();
//        return !$ret['return'] ? \Create::error($ret['info']) : \Create::success([self::RESULT_FILE=>[self::KEY_URL=>'https://file.bazaaar.org'.'/'.$fileDir.$ret['finalFileName'],self::KEY_FILE=>$ret['finalDirFileName']]]);

        // oss
        $upload = new UploadOss($type_arr,$fileDir,$maxSize);
        $ret = $upload->upload_file();
        return !$ret['return'] ? \Create::error($ret['info']) : \Create::success([self::RESULT_FILE=>[self::KEY_URL=>$ret['finalHOst'].'/'.$ret['finalDirFileName'],self::KEY_FILE=>$ret['finalDirFileName']]]);
    }


}
