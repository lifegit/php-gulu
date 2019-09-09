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

class ProjectImgModel extends BaseModel {
    use SqlUtils;


    //keys
    const KEY_ID = 'id';
    const KEY_PROJECTID = 'projectid';
    const KEY_IMG = 'img';


    const RESULT_IMGS = 'imgs';


    public function getAllList($whereList,$selectList = []){
        $listWhere = $this->listToSqlWhere($whereList);
        $listSelect = $this->listToSqlSelect($selectList);
        $res = $this->mysql->selectAll("select $listSelect FROM `tb_projects_img` ${listWhere['sql']} order by time_created desc",$listWhere['arr']);
        return \Create::success([self::RESULT_IMGS => $res]);
    }

}
