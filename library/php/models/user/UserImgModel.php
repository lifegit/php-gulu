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

class UserImgModel extends BaseModel {
    use SqlUtils;


    //keys
    const KEY_ID = 'id';
    const KEY_USERID = 'userid';
    const KEY_IMG = 'img';
    const KEY_TIME_CREATED = 'time_created';

    const RESULT_IMG = 'img';
    const RESULT_IMGS = 'imgs';


    public function getAllList($whereList,$selectList = [],$isGroup = false){
        $listWhere = $this->listToSqlWhere($whereList);
        $listSelect = $this->listToSqlSelect($selectList);
        $sql = $isGroup ? 'group by userid' : 'order by time_created desc';
        $res = $this->mysql->selectAll("select $listSelect FROM `tb_users_match_img` ${listWhere['sql']} $sql",$listWhere['arr']);
        return \Create::success([self::RESULT_IMGS => $res]);
    }

    public function getInfo($whereList,$selectList = []){
        $listWhere = $this->listToSqlWhere($whereList);
        $listSelect = $this->listToSqlSelect($selectList);
        $row = $this->mysql->select("SELECT $listSelect FROM `tb_users_match_img` ${listWhere['sql']} limit 1",$listWhere['arr']);
        return $row === false ? \Create::error('记录不存在') : \Create::success([self::RESULT_IMG=>$row]);
    }

    public function add($userid,$img){
        $row = $this->mysql->execs("INSERT INTO `tb_users_match_img` (`userid`,`img`) VALUES (:userid,:img)",array(':userid'=>$userid,':img'=>$img));
        return $row === 0 ? \Create::error('添加失败') : \Create::success([self::KEY_ID => $this->mysql->lastInsertId()], '添加成功');
    }
}
