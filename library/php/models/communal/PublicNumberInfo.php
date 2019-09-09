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

class PublicNumberInfo extends BaseModel {
    use SqlUtils;


    //keys
    const KEY_ID = 'id';
    const KEY_USE = 'use';
    const KEY_QR = 'qr';
    const KEY_APPID = 'appid';


    const RESULT_INFO = 'publicNumber';


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
        $row = $this->mysql->select("SELECT $listSelect FROM `tb_publicnumbers_info` ${listWhere['sql']} limit 1", $listWhere['arr']);
        return $row === false ? \Create::error('公众号不存在') : \Create::success([self::RESULT_INFO => $row]);
    }



}
