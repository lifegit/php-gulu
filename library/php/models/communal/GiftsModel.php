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

class GiftsModel extends BaseModel {
    use SqlUtils;



    //keys
    const KEY_ID = 'id';
    const KEY_GOOD = 'good';
    const KEY_IMG = 'img';
    const KEY_MONEY = 'money';
    const KEY_VOTES = 'votes';
    const KEY_TIME_CREATED = 'time_created';



    const RESULT_GIFTS = 'gifts';


    /**
     * 查询列表
     * @param array $list
     * @param array $filtered
     * @param array $searched
     * @param array $sorted
     * @return array
     */
    public function getList($list=[],$filtered=[],$searched=[],$sorted=[]){
        $resMerge = $this->merge(['data'=>$filtered,'allow'=>[self::KEY_ID]],['data'=>$searched,'isVague'=>false,'allow'=>[]]);
        $resSort = $this->sortToString($sorted,[],[self::KEY_ID=>'asc']);
        $listSelect = $this->listToSqlSelect($list);
        $res = $this->mysql->selectAll("select $listSelect FROM `tb_gifts_info`".$resMerge['str']." {$resSort} ",$resMerge['arr']);
        return \Create::success([self::RESULT_GIFTS=>$res]);
    }
}
