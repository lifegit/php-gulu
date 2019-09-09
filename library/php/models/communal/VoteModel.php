<?php
/**
 * Created by PhpStorm.
 * User: yxs
 * Date: 2019-06-25
 * Time: 09:56
 */

namespace models\communal;

use Exception;
use libs\Hashids;
use libs\SqlUtils;
use models\BaseModel;
use models\user\UserInfoModel;
use PDO;

class VoteModel extends BaseModel {
    use SqlUtils;


    const TYPES_TYPE_VOTE = 1;
    const TYPES_TYPE_GIFT = 2;


    //keys
    const KEY_ID = 'id';
    const KEY_TYPE = 'type';
    const KEY_OPENID = 'openid';
    const KEY_PROJECTID = 'projectid';
    const KEY_UID = 'uid';
    const KEY_NUM = 'NUM';
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
        $row = $this->mysql->select("SELECT $listSelect FROM `tb_votes_records` ${listWhere['sql']} limit 1", $listWhere['arr']);
        return $row === false ? \Create::error('信息不存在') : \Create::success([self::RESULT_INFO => $row]);
    }


    public function add($type,$openid,$projectId,$uid,$num){
        $row = $this->mysql->execs("INSERT INTO `tb_votes_records` (`type`,`openid`,`projectid`,`uid`,`num`) VALUES (:type,:openid,:projectid,:uid,:num)",array(':type'=>$type,':openid'=>$openid,':projectid'=>$projectId,':uid'=>$uid,':num'=>$num));
        return $row === 0 ? \Create::error('添加失败') : \Create::success([self::KEY_UID => $uid], '添加成功');
    }

    public function vote($projectId,$uid,$openid,$type,$num){
        $error = false;
        try{
            //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    //设置错误模式,发生错误时可在catch里抛出异常,用于debug
            $this->mysql->beginTransaction();//开启事务处理 FOR UPDATE 可防止超卖问题,表引擎请用InnoDB,原理为(1.同一份资源InnoDB会行锁,2.FOR UPDATE 会锁住同一资源下的事务,只有前事务完成后事务才会触发)


            if($type == self::TYPES_TYPE_VOTE){
                ini_set('date.timezone','Asia/Shanghai');
                $startDate = date("Y-m-d",time());
                $endDate = $startDate.' 23:59:59';
                $res = $this->getInfo([self::KEY_TYPE => self::TYPES_TYPE_VOTE,self::KEY_PROJECTID => $projectId,self::KEY_UID => $uid,self::KEY_OPENID => $openid,self::KEY_TIME_CREATED=>['sql'=>    self::KEY_TIME_CREATED." >='$startDate' AND ".self::KEY_TIME_CREATED." <='$endDate'"] ]);
                if(\Create::isSuccess($res))
                    throw new Exception("今日已投过票,请明天再投!");
            }

            // 添加投票记录
            $res = $this->add($type,$openid,$projectId,$uid,$num);
            if(!\Create::isSuccess($res))
                throw new Exception("添加投票记录失败");


            //user add num
            $res = (new UserInfoModel())->setInfo([UserInfoModel::KEY_PROJECTID => $projectId , UserInfoModel::KEY_UID => $uid],[
                UserInfoModel::KEY_NUM => ['sql' => UserInfoModel::KEY_NUM  . '+:' . UserInfoModel::KEY_NUM  ,'arr' => [':'.UserInfoModel::KEY_NUM => $num]],
            ]);
            if(! \Create::isSuccess($res))
                throw new Exception($res['info']);



            //project add num
            $res = (new ProjectModel())->setInfo($projectId,[
                ProjectModel::KEY_NUM => ['sql' => ProjectModel::KEY_NUM  . '+:' . ProjectModel::KEY_NUM  ,'arr' => [':'.ProjectModel::KEY_NUM => $num]],
            ]);
            if(! \Create::isSuccess($res))
                throw new Exception($res['info']);



            $this->mysql->commit();//提交
        }catch(Exception $e){
            $this->mysql->rollback();//发生错误,回滚事务
            $error = $e->getMessage();
        }
        if($error !== false)
            return \Create::error($error);

        $this->mysql->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);//自动提交,如果最后不自动提交,是不执行的

        return \Create::success([],'投票成功');

    }


}
