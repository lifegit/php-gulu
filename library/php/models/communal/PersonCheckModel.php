<?php
namespace models\communal;
use models\BaseModel;
//根据用户判断是否需要验证码(返回rsa私钥后的salt与code，用username与code保存salt和code)
//                |
//           rsa公钥解密salt
//      |                 |
//    不需要验证码       需要验证码
//      |                 |
//      |              带username和code获取验证码
//                |
//               登录
// 带username和code和加密后password[和验证码]
// 判断是否需要验证码->验证验证码
// 解密密码,验证账号密码
// 删除这个账号的验证码和salt
class PersonCheckModel extends BaseModel {

    const PERSONCHECK = 'pc:';
    const OVERDUE_TIME = 180; // 过期时间(单位:秒) 3分钟

    const KEY_SALT = 's';
    const KEY_CODE = 'c';
    const KEY_VCODE = 'v';

    const RESULT_PC = 'pc';

    private $person;
    private $isTtl = false;

    public function __construct($personName, $personType = null){
        $this->person = $personType ? "$personType:$personName" : $personName;
    }

    /**
     * isEmpty
     * @return bool
     */
    public function isEmpty(){
        return $this->redis->EXISTS(self::PERSONCHECK . $this->person);
    }


    /**
     * setting
     * @param array $data
     * @return bool
     */
    public function setData($data){
        $is = $this->redis->HMSET(self::PERSONCHECK . $this->person,$data);
        if(!$this->isTtl){
            if(!$this->isTtl && $this->redis->pttl(self::PERSONCHECK . $this->person) === -1){
                $this->redis->Expire(self::PERSONCHECK . $this->person,self::OVERDUE_TIME);
            }
            $this->isTtl = true;
        }

        return $is === true;
    }

    /**
     * getting
     * @param array $data
     * @return array
     */
    public function getData($data){
        return $this->redis->HMGET(self::PERSONCHECK . $this->person,$data);
    }

    /**
     * destroy
     */
    public function destroy(){
        return $this->redis->DEL(self::PERSONCHECK . $this->person);
    }
}
?>