<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/10
 * Time: 20:00
 */


class dies
{
    static function die($data){
        die(json_encode($data));
    }
    static function error($info,$data = []){
        die(json_encode(Create::error($info,$data)));
    }
    static function success($data = [],$info){
        die(json_encode( Create::success($data,$info)));
    }
}