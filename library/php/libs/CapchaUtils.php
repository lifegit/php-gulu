<?php
/**
 * Created by PhpStorm.
 * User: yxs
 * Date: 2018-12-28
 * Time: 11:22 PM
 */

namespace libs;

/**
 * Trait CapchaUtils
 * $this->redis 报错是因为这里没上下文，该trait要在一个BaseModel的class里。
 * @package libs
 */
trait CapchaUtils{

    private function setCapcha($key,$phone){
        $rand = new Rand();
        $code = $rand->getRandChar(Rand::TYPE_NUM,6);

        $res = (new \models\communal\SimpleSmsModel())->sendForgetPassword($phone,$code);
        if(!\Create::isSuccess($res))
            return $res;


        $this->redis->set($key, $code);
        $this->redis->expire($key,900);

        return \Create::success([],'成功');
    }

    private function getCapcha($key){
        $capcha = $this->redis->get($key);
        if(! $capcha)
            return \Create::error('不存在验证码');
        $this->redis->del($key);
        return \Create::success(['capcha'=>$capcha]);
    }
}