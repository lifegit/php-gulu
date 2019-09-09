<?php
namespace models\communal;
use models\BaseModel;

//$verificationCodeModel = new VerificationCodeModel('agent' . 1);
//if(login...){
//    $this->delFrequently();
//}else{
//    if($this->addCount())
//        echo 'Need VerificationCode';
//}


/**
 * 验证码获取次数
 */ 
class VerificationCodeModel extends BaseModel {

    const VERIFICATIONCODE = 'vc:';
    const OVERDUE_TIME = 1800; // 连续获取的最大时间的过期时间(单位:秒) 30分钟
    const MAX = 3; // 最大数量
    const RESULT_VC = 'vc';

    private $person;

    public function __construct($personName, $personType = null){
        $this->person = $personType ? "$personType:$personName" : $personName;
    }

    /**
     * 是否频繁
     * @return bool
     */
    public function isFrequently(){
        $num = $this->redis->GET(self::VERIFICATIONCODE . $this->person) || 0;
        return $num >= self::MAX;
    }

    /**
     * 增加一个次数,并且返回增加次数后是否频繁
     * @return bool
     */
    public function addCount(){
        $num = $this->redis->INCR(self::VERIFICATIONCODE . $this->person);
        if($num === 1){
            $this->redis->EXPIRE(self::VERIFICATIONCODE . $this->person, self::OVERDUE_TIME);
        }
        return $num >= self::MAX;
    }

    /**
     * 删除频繁状态
     */
    public function delFrequently(){
        $this->redis->del(self::VERIFICATIONCODE . $this->person);
    }
}
?>