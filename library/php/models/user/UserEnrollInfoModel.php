<?php
namespace models\user;
use Exception;
use libs\CapchaUtils;
use libs\Paging;
use libs\Rand;
use libs\Rc4Code;
use libs\SqlUtils;
use models\BaseModel;
use models\communal\ConfigBaseModel;
use models\communal\SpreadModel;
use PDO;

/**
 * Class AgentInfoModel
 * 代理_信息
 *
 * @package models\agent
 * @date : 2018-10-27
 * @author : The life
 * @version : 1.0
 */
class UserEnrollInfoModel extends BaseModel
{

    use SqlUtils;

    //keys
    const KEY_ID = 'id';
    const KEY_PROJECTID = 'projectid';
    const RESULT_INFO = 'info';

    public function add($project,$name,$text,$mobile,$openid){
//        $row = $this->mysql->select("SELECT IFNULL(max(id),0) as len FROM tb_users_match_info WHERE projectid=:projectid", array(':projectid'=>$project));
//        $uid = $row['len'] + 1;
        $row = $this->mysql->execs("INSERT INTO `tb_users_enroll_info` (`projectid`,`text`,`name`,`mobile`,`openid`) VALUES (:projectid,:text,:name,:mobile,:openid)",array(':projectid'=>$project,':text'=>$text,':name'=>$name,':mobile'=>$mobile,':openid'=>$openid));
        return $row === 0 ? \Create::error('添加失败') : \Create::success([self::KEY_ID => $this->mysql->lastInsertId()], '添加成功');
    }


    /**
     * 获取普通信息
     * @param array $whereList
     * @param array $selectList
     * @return array
     */
    public function getInfo($whereList, $selectList = [])
    {
        $listWhere = $this->listToSqlWhere($whereList);
        $listSelect = $this->listToSqlSelect($selectList);
        $row = $this->mysql->select("SELECT $listSelect FROM `tb_users_enroll_info` ${listWhere['sql']} limit 1", $listWhere['arr']);
        return $row === false ? \Create::error('用户不存在') : \Create::success([self::RESULT_INFO => $row]);
    }

}
