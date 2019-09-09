<?php
require_once __DIR__ . '/../../../../../library/php/libs/Autoloader.php';//自动载入class


//if(empty($_POST['name']) || empty($_POST['mobile']) || empty($_POST['idcard']))
//    dies::error('参数不符');
//
//$name = $_POST['name'];
//$mobile = $_POST['mobile'];
//$idcard = $_POST['idcard'];

if(empty($_POST['name']))
    dies::error('参数不符');

$name = $_POST['name'];


$admin = models\admin\Admin_sessionModel::getInstance();
if(! $admin->isLogin(($adminId = $admin->getID())))
    dies::die($admin->notLogin());


use \models\admin\AdminInfoModel;

$res = (new AdminInfoModel())->setInfo($adminId,[
    AdminInfoModel::KEY_NAME  => $name,
//    AdminInfoModel::KEY_MOBILE  => $mobile,
//    AdminInfoModel::KEY_IDCARD  => $idcard,
]);

//点点 log

dies::die($res);
