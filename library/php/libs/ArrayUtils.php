<?php
namespace libs;

class ArrayUtils{
    public function ListToString($list){
        return $list===[] ? '1' : '`'.implode('`,`',$list).'`';
    }
}