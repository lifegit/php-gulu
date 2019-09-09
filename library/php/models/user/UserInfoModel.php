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
use models\communal\ProjectModel;
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
class UserInfoModel extends BaseModel
{
    use SqlUtils;
    use CapchaUtils;

    const SIGN = 'u';

    //keys
    const KEY_ID = 'id';
    const KEY_PROJECTID = 'projectid';
    const KEY_UID = 'uid';
    const KEY_USE = 'use';
    const KEY_NAME = 'name';
    const KEY_MOBILE = 'mobile';
    const KEY_TEXT = 'text';
    const KEY_NUM = 'num';
    const KEY_VISIT = 'visit';
    const KEY_TIME_CREATED = 'time_created';

    const RESULT_INFO = 'info';

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
        $row = $this->mysql->select("SELECT $listSelect FROM `tb_users_match_info` ${listWhere['sql']} limit 1", $listWhere['arr']);
        return $row === false ? \Create::error('用户不存在') : \Create::success([self::RESULT_INFO => $row]);
    }

    /**
     * 查询列表
     * @param array $list
     * @param array $filtered
     * @param array $searched
     * @param array $sorted
     * @return array
     */
    public function getList($list = [], $filtered = [], $searched = [], $sorted = [])
    {
        $resMerge = $this->merge(['data' => $filtered, 'allow' => [self::KEY_ID,self::KEY_PROJECTID]], ['data' => $searched, 'isVague' => false, 'allow' => [self::KEY_PROJECTID]]);
        $resSort = $this->sortToString($sorted, [], [self::KEY_NUM => 'desc']);
        $listSelect = $this->listToSqlSelect($list);
        $res = $this->mysql->selectAll("select $listSelect FROM `tb_users_match_info`" . $resMerge['str'] . " {$resSort} ", $resMerge['arr']);
        return \Create::success(['data' => $res]);
    }

    /**
     * 设置普通信息
     * @param $whereList
     * @param array $updateList
     * @return array
     */
    public function setInfo($whereList, $updateList)
    {
        if (!count($updateList) || !count($whereList))
            return \Create::error('无修改项');
        $listWhere = $this->listToSqlWhere($whereList);
        $listUpdate = $this->listToSqlUpdate($updateList);
        $row = $this->mysql->execs("UPDATE tb_users_match_info SET ${listUpdate['sql']} ${listWhere['sql']} limit 1", $listUpdate['arr'] + $listWhere['arr']);
        return $row === 0 ? \Create::error('修改失败') : \Create::success([], '修改成功');
    }




}
