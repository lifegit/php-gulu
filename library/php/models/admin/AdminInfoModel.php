<?php
namespace models\admin;
use Exception;
use libs\CapchaUtils;
use libs\Paging;
use libs\Rand;
use libs\SqlUtils;
use models\BaseModel;
use PDO;

/**
 * Class AdminInfoModel
 *
 * @package models\agent
 * @date : 2018-10-27
 * @author : The life
 * @version : 1.0
 */
class AdminInfoModel extends BaseModel {
    use SqlUtils;
    use CapchaUtils;

    const SIGN = 'h';


    //keys
    const KEY_ID = 'id';
    const KEY_USERNAME = 'username';
    const KEY_PASSWORD = 'password';
    const KEY_AVATAR = 'avatar';
    const KEY_NAME = 'name';
    const KEY_USE = 'use';
    const KEY_TIME = 'time';



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
            $row = $this->mysql->select("SELECT count(*) as len FROM `tb_admins_info` WHERE id in ${listIn['sql']}",$listIn['arr']);
            return $row === false || $row['len'] != count($listIn['arr']) ? \Create::error('用户不存在') : \Create::success([self::RESULT_EXIST=>true]);
        }else if(count($agentIdArr) == 1){
            $row = $this->mysql->select('SELECT 1 FROM `tb_admins_info` WHERE id=:userid limit 1',array(':userid'=>$agentIdArr[0]));
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
        $row = $this->mysql->select("SELECT $listSelect FROM `tb_admins_info` ${listWhere['sql']} limit 1",$listWhere['arr']);
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
        $row = $this->mysql->select("SELECT $listSelect FROM `tb_admins_info` WHERE username=:username limit 1",array(':username'=>$username));
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
    public function getList($list=[],$page=1,$filtered=[],$searched=[],$sorted=[]){
        $paging = new Paging($page);
        $resMerge = $this->merge(['data'=>$filtered,'allow'=>[self::KEY_ID]],['data'=>$searched,'isVague'=>false,'allow'=>[]]);
        $row = $this->mysql->select("select count(*)as len FROM `tb_admins_info`".$resMerge['str'],$resMerge['arr']);
        $paging->setAllLength($row['len']);
        $paging = $paging->getPaging();
        $resSort = $this->sortToString($sorted,[],['id'=>'desc']);
        $listSelect = $this->listToSqlSelect($list);
        $res = $row['len'] <= 0 ?  [] : $this->mysql->selectAll("select $listSelect FROM `tb_admins_info`".$resMerge['str']." {$resSort} ".$paging[Paging::Limit],$resMerge['arr']);
        return \Create::success(['data'=>$res, Paging::PAGE=>[Paging::AllLength=>$paging[Paging::AllLength],Paging::PageLength=>$paging[Paging::PageLength]]]);
    }

    /**
     * 设置普通信息
     * @param $adminId
     * @param array $updateList
     * @return array
     */
    public function setInfo($adminId,$updateList){
        if(! count($updateList))
            return \Create::error('无修改项');

        $arr = array(':adminId'=>$adminId);

        $listUpdate = $this->listToSqlUpdate($updateList);
        $row = $this->mysql->execs("UPDATE tb_admins_info SET ${listUpdate['sql']} WHERE id=:adminId limit 1",$listUpdate['arr'] + $arr);
        return $row === 0 ? \Create::error('修改失败') : \Create::success([],'修改成功');
    }

    public function newPass($user_id,$password){
        $passwordModel = new \models\communal\PasswordModel();
        $temp = $passwordModel->make($password);
        if(!\Create::isSuccess($temp))
            return \Create::error('系统错误');
        $password = $temp[\models\communal\PasswordModel::HASH];

        $row = $this->mysql->execs(" UPDATE tb_admins_info SET password=:newPass WHERE id=:user_id limit 1",array(':user_id'=>$user_id,':newPass'=>$password));
        return $row !== 1 ? \Create::error('新密码不能和旧密码一样') : \Create::success([],'修改成功');
    }

    public function register($username,$password,$name){
        $passwordModel = new \models\communal\PasswordModel();
        $temp = $passwordModel->make($password);
        if(!\Create::isSuccess($temp))
            return \Create::error('系统错误');
        $password = $temp[\models\communal\PasswordModel::HASH];

        $row = $this->mysql->execs("INSERT INTO `tb_admins_info`(`username`, `password`,`name`) VALUES (:username,:password,:name)",array(':username'=>$username,':password'=>$password,':name'=>$name));
        if($row !== 1)
            return \Create::error('此帐号已被注册');

        return  \Create::success([],'注册成功');
    }


}
