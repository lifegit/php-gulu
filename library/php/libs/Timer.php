<?php
/**
 * Created by PhpStorm.
 * User: yxs
 * Date: 2018-9-30
 * Time: 上午2:37
 * 一个测试代码效率的class
 */

namespace libs;

//demo
//$timer = new timer();
//$timer->start();
////测试代码
//$timer->stop();
//echo "time:".$timer->spent();

class Timer {
    private $StartTime = 0;//程序运行开始时间
    private $StopTime  = 0;//程序运行结束时间
    private $TimeSpent = 0;//程序运行花费时间

    //程序运行开始
    function start(){
        $this->StartTime = microtime(true);
    }

    //程序运行结束
    function stop(){
        $this->StopTime = microtime(true);
    }
    //程序运行花费的时间
    //返回获取到的程序运行时间差 (毫秒)
    function spent(){
        $this->TimeSpent=$this->StopTime-$this->StartTime;
        return  number_format($this->TimeSpent*1000, 4).'Millisecond';
    }
}
