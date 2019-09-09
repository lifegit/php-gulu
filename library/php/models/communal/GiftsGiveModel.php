<?php
/**
 * Created by PhpStorm.
 * User: yxs
 * Date: 2019-06-25
 * Time: 09:56
 */

namespace models\communal;

use libs\SqlUtils;
use models\BaseModel;

class GiftsGiveModel extends BaseModel {
    use SqlUtils;


    //keys
    const KEY_ID = 'id';
    const KEY_PROJECTID = 'projectid';
    const KEY_UID = 'uid';
    const KEY_GIFTID = 'giftid';
    const KEY_NAME = 'name';
    const KEY_AVATAR = 'avatar';
    const KEY_TIME_CREATED = 'time_created';


    const RESULT_GIVES = 'gives';


    /**
     * 查询列表
     * @param array $list
     * @param array $filtered
     * @param array $searched
     * @param array $sorted
     * @return array
     */
    public function getList($list=[],$filtered=[],$searched=[],$sorted=[]){
        $resMerge = $this->merge(['data'=>$filtered,'allow'=>[self::KEY_ID]],['data'=>$searched,'isVague'=>false,'allow'=>[self::KEY_UID,self::KEY_PROJECTID]]);
        $resSort = $this->sortToString($sorted,[],[self::KEY_TIME_CREATED=>'desc']);
        $listSelect = $this->listToSqlSelect($list);
        $res = $this->mysql->selectAll("select $listSelect FROM `tb_gifts_records`".$resMerge['str']." {$resSort} ",$resMerge['arr']);
        return \Create::success([self::RESULT_GIVES=>$res]);
    }

    public function add($projectid,$uid,$giftid,$name,$avatar){
        $row = $this->mysql->execs("INSERT INTO `tb_gifts_records` (`projectid`,`uid`,`giftid`,`name`,`avatar`) VALUES (:projectid,:uid,:giftid,:name,:avatar)",array(':projectid'=>$projectid,':uid'=>$uid,':giftid'=>$giftid,':name'=>$name,':avatar'=>$avatar));
        return $row === 0 ? \Create::error('添加失败') : \Create::success([self::KEY_ID => $this->mysql->lastInsertId()], '添加成功');
    }
}
