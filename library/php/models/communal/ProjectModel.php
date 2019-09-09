<?php
/**
 * Created by PhpStorm.
 * User: yxs
 * Date: 2019-06-25
 * Time: 09:56
 */

namespace models\communal;

use libs\Hashids;
use libs\SqlUtils;
use models\BaseModel;

class ProjectModel extends BaseModel {
    use SqlUtils;


    //keys
    const KEY_ID = 'id';
    const KEY_NUM = 'num';
    const KEY_USE = 'use';
    const KEY_HASHID = 'hashid';
    const KEY_TITLE = 'title';
    const KEY_AGENTID = 'agentid';
    const KEY_EXPLAIN = 'explain';
    const KEY_PRIZE = 'prize';
    const KEY_VISIT = 'visit';
    const KEY_TIME_VOTE_STARTED = 'time_vote_started';
    const KEY_TIME_VOTE_ENDED = 'time_vote_ended';
    const KEY_TIME_ENROLL_STARTED = 'time_enroll_started';
    const KEY_TIME_ENROLL_ENDED = 'time_enroll_ended';
    const KEY_TIME_CREATED = 'time_created';
    const KEY_PUBLICNUMBER = 'publicnumber';

    const RESULT_INFO = 'info';



    public static function idCode($text,$isEncode = false){
        $hashids = Hashids::instance(6,'id');
        return $isEncode ? $hashids->encode($text) : $hashids->decode($text);
    }

    public function getInfo($whereList,$selectList = []){
        $listWhere = $this->listToSqlWhere($whereList);
        $listSelect = $this->listToSqlSelect($selectList);
        $row = $this->mysql->select("SELECT $listSelect FROM `tb_projects_info` ${listWhere['sql']} limit 1",$listWhere['arr']);
        return $row === false ? \Create::error('记录不存在') : \Create::success([self::RESULT_INFO=>$row]);
    }

    /**
     * 设置普通信息
     * @param $projectId
     * @param array $updateList
     * @return array
     */
    public function setInfo($projectId,$updateList){
        if(! count($updateList))
            return \Create::error('无修改项');

        $arr = array(':id'=>$projectId);

        $listUpdate = $this->listToSqlUpdate($updateList);
        $row = $this->mysql->execs("UPDATE `tb_projects_info` SET ${listUpdate['sql']} WHERE id=:id limit 1",$listUpdate['arr'] + $arr);
        return $row === 0 ? \Create::error('修改失败') : \Create::success([],'修改成功');
    }

    /**
     * 判断是否过期
     * @param $time
     * @return bool
     */
    public function checkOverdue($time){
        ini_set('date.timezone','Asia/Shanghai');
        return time() <= strtotime($time);
    }


}
