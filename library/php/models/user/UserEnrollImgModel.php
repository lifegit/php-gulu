<?php
/**
 * Created by PhpStorm.
 * User: yxs
 * Date: 2019-06-25
 * Time: 09:56
 */

namespace models\user;

use libs\SqlUtils;
use models\BaseModel;

class UserEnrollImgModel extends BaseModel {
    use SqlUtils;


    //keys
    const KEY_ID = 'id';
    const KEY_USERID = 'userid';
    const KEY_IMG = 'img';
    const KEY_TIME_CREATED = 'time_created';

    const RESULT_IMG = 'img';
    const RESULT_IMGS = 'imgs';



    public function add($userid,$img){
        $row = $this->mysql->execs("INSERT INTO `tb_users_enroll_img` (`userid`,`img`) VALUES (:userid,:img)",array(':userid'=>$userid,':img'=>$img));
        return $row === 0 ? \Create::error('添加失败') : \Create::success([self::KEY_ID => $this->mysql->lastInsertId()], '添加成功');
    }
}
