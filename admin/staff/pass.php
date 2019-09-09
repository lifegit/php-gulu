<?php
require_once __DIR__ . '/../../../../library/php/libs/Autoloader.php';//自动载入class

if(empty($_REQUEST['newPass']) || empty($_REQUEST['id']))
    dies::error('参数不符');
$id = $_REQUEST['id'];
$newPass = $_REQUEST['newPass'];
if(strlen($newPass)<6)
    dies::error('新密码长度需长于6位');
if (preg_match('/[^\x00-\x80]/', $newPass))
    dies::error('新密码不运行含有中文');


$admin = models\admin\Admin_sessionModel::getInstance();
if(! $admin->isLogin(($adminId = $admin->getID())))
    dies::die($admin->notLogin());
if($adminId != 1)
    dies::error('权限不足');


$adminInfoModel = new \models\admin\AdminInfoModel();
$res = $adminInfoModel->newPass($id,$newPass);

dies::die($res);
