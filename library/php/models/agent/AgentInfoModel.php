<?php
namespace models\agent;
use Exception;
use libs\CapchaUtils;
use libs\Paging;
use libs\Rand;
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
class AgentInfoModel extends BaseModel {
    use SqlUtils;
    use CapchaUtils;

    const SIGN = 'a';


    //keys
    const KEY_ID = 'id';
    const KEY_USERNAME = 'username';
    const KEY_PASSWORD = 'password';
    const KEY_MONEY = 'money';
    const KEY_TIME = 'time';
    const KEY_SUPERIOR = 'superior';
    const KEY_SPREADMONEY = 'spreadmoney';
    const KEY_NUM = 'num';
    const KEY_USE = 'use';
    const KEY_SUCCESSNUM = 'successnum';
    const KEY_FAILNUM = 'failnum';
    const KEY_FROZENPROJECT = 'frozenproject';
    const KEY_CREDIT = 'credit';


    const RESULT_INFO = 'info';
    const RESULT_EXIST = 'exist';


    /**
     * 批量验证id是否都存在,都存在返回true，某一个不存在返回false
     * @param $agentIdArr
     * @return array
     */
    public function isExist($agentIdArr = []){
        if(count($agentIdArr) > 1){
            $listIn = $this->listToSqlIn($agentIdArr);
            $row = $this->mysql->select("SELECT count(*) as len FROM `tb_agents_info` WHERE id in ${listIn['sql']}",$listIn['arr']);
            return $row === false || $row['len'] != count($listIn['arr']) ? \Create::error('用户不存在') : \Create::success([self::RESULT_EXIST=>true]);
        }else if(count($agentIdArr) == 1){
            $row = $this->mysql->select('SELECT 1 FROM `tb_agents_info` WHERE id=:userid limit 1',array(':userid'=>$agentIdArr[0]));
            return $row === false ? \Create::error('用户不存在') : \Create::success([self::RESULT_EXIST=>true]);
        }else
            return \Create::error('系统错误');
    }

    /**
     * 获取普通信息
     * @param array $whereList
     * @param array $selectList
     * @return array
     */
    public function getInfo($whereList,$selectList = []){
        $listWhere = $this->listToSqlWhere($whereList);
        $listSelect = $this->listToSqlSelect($selectList);
        $row = $this->mysql->select("SELECT $listSelect FROM `tb_agents_info` ${listWhere['sql']} limit 1",$listWhere['arr']);
        return $row === false ? \Create::error('用户不存在') : \Create::success([self::RESULT_INFO=>$row]);
    }

    /**
     * 获取普通信息
     * @param $username
     * @param array $list
     * @return array
     */
    public function getUserNameInfo($username,$list = []){
        $listSelect = $this->listToSqlSelect($list);
        $row = $this->mysql->select("SELECT $listSelect FROM `tb_agents_info` WHERE username=:username limit 1",array(':username'=>$username));
        return $row === false ? \Create::error('用户不存在') : \Create::success([self::RESULT_INFO=>$row]);
    }
    /**
     * 查询 代理 列表
     * @param array $list
     * @param int $page
     * @param array $filtered
     * @param array $searched
     * @param array $sorted
     * @return array
     */
    public function getAgentList($list=[],$page=1,$filtered=[],$searched=[],$sorted=[]){
        $paging = new Paging($page);
        $resMerge = $this->merge(['data'=>$filtered,'allow'=>[self::KEY_ID]],['data'=>$searched,'isVague'=>false,'allow'=>[self::KEY_USERNAME]]);
        $row = $this->mysql->select("select count(*)as len FROM `tb_agents_info`".$resMerge['str'],$resMerge['arr']);
        $paging->setAllLength($row['len']);
        $paging = $paging->getPaging();
        $resSort = $this->sortToString($sorted,[self::KEY_MONEY,self::KEY_TIME,self::KEY_NUM,self::KEY_FROZENPROJECT,self::KEY_SUCCESSNUM],['id'=>'desc']);
        $listSelect = $this->listToSqlSelect($list);
        $res = $row['len'] <= 0 ?  [] : $this->mysql->selectAll("select $listSelect FROM `tb_agents_info`".$resMerge['str']." {$resSort} ".$paging[Paging::Limit],$resMerge['arr']);
        return \Create::success(['data'=>$res, Paging::PAGE=>[Paging::AllLength=>$paging[Paging::AllLength],Paging::PageLength=>$paging[Paging::PageLength]]]);
    }

    /**
     * 设置普通信息
     * @param $agentId
     * @param array $updateList
     * @return array
     */
    public function setInfo($agentId,$updateList){
        if(! count($updateList))
            return \Create::error('无修改项');

        $arr = array(':agentId'=>$agentId);

        $listUpdate = $this->listToSqlUpdate($updateList);
        $row = $this->mysql->execs("UPDATE tb_agents_info SET ${listUpdate['sql']} WHERE id=:agentId limit 1",$listUpdate['arr'] + $arr);
        return $row === 0 ? \Create::error('修改失败') : \Create::success([],'修改成功');
    }


    public function register($username,$password,$code){
        $res = $this->getInfo([self::KEY_USERNAME => $username]);
        if(\Create::isSuccess($res))
            return \Create::error('该账号已被注册');

        $superior = 0;
        if($code){
            $res = (new ConfigBaseModel())->getInfo([ConfigBaseModel::KEY_CODE]);
            if(! \Create::isSuccess($res))
                return $res;
            if($res[ConfigBaseModel::RESULT_CONFIG][ConfigBaseModel::KEY_CODE] === ConfigBaseModel::TYPE_CODE_TRUE){
                $superior = (new SpreadModel())->code(SpreadModel::KEYS_USER,$code,false);
                $res = $this->getInfo([self::KEY_ID => $superior]);
                if(! \Create::isSuccess($res))
                    return $res;
            }
        }

        $passwordModel = new \models\communal\PasswordModel();
        $temp = $passwordModel->make($password);
        if(!\Create::isSuccess($temp))
            return \Create::error('系统错误');
        $password = $temp[\models\communal\PasswordModel::HASH];

        $row = $this->mysql->execs("INSERT INTO `tb_agents_info`(`username`, `password`,`superior`) VALUES (:username,:password,:superior)",array(':username'=>$username,':password'=>$password,':superior'=>$superior));
        if($row !== 1)
            return \Create::error('注册失败');

        return  \Create::success([],'注册成功');
    }

}
