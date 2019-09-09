<?php
require_once __DIR__ . '/../../../../library/php/libs/Autoloader.php';//自动载入class
use \models\admin\AdminInfoModel;


$admin = models\admin\Admin_sessionModel::getInstance();
if(! $admin->isLogin(($adminId = $admin->getID())))
    dies::die($admin->notLogin());

$res = [];

$adminInfoModel = new AdminInfoModel();
$temp = $adminInfoModel->getInfo([AdminInfoModel::KEY_ID => $adminId],[AdminInfoModel::KEY_AVATAR,AdminInfoModel::KEY_NAME,AdminInfoModel::KEY_TIME]);
! Create::isSuccess($temp) ? dies::die($temp) : $res += $temp;



dies::die($res);
