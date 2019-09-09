<?php
require_once __DIR__ . '/../../../../library/php/libs/Autoloader.php';//自动载入class

if(empty($_REQUEST['use']) || empty($_REQUEST['id']))
    dies::error('参数不符');
$id = $_REQUEST['id'];
$use = libs\StringUtils::bool($_REQUEST['use']) ? 1 : 0;


$admin = models\admin\Admin_sessionModel::getInstance();
if(! $admin->isLogin(($adminId = $admin->getID())))
    dies::die($admin->notLogin());
if($adminId != 1)
    dies::error('权限不足');


use  \models\admin\AdminInfoModel;
$adminInfoModel = new AdminInfoModel;
$res = $adminInfoModel->setInfo($id,[AdminInfoModel::KEY_USE => $use]);

dies::die($res);
