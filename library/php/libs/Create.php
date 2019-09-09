<?php

class Create{
    static function isSuccess($data){
        return $data['success'] ? true : false;
    }
    static function success($data = [],$info = ''){
        $data['success']= true;
        if($info != '')
            $data['info']  = $info;
        return $data;
    }
    static function error($info = '',$data = []){
        $data['success'] = false;
        $data['info'] = $info;
        return $data;
    }
}
