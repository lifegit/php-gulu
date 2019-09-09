<?php
/**
 * Created by PhpStorm.
 * User: yxs
 * Date: 2019-05-30
 * Time: 13:20
 */
require_once __DIR__ . '/../../../../library/php/libs/Autoloader.php';//自动载入class

if( empty($_POST['username'])|| empty($_POST['password'])|| empty($_POST['name']))
    dies::error('参数不符');

$username = $_POST['username'];
$password = $_POST['password'];
$name = $_POST['name'];

$admin = models\admin\Admin_sessionModel::getInstance();
if(! $admin->isLogin(($adminId = $admin->getID())))
    dies::die($admin->notLogin());
if($adminId != 1)
    dies::error('权限不足');

$userInfoModel = new \models\admin\AdminInfoModel();
$res = $userInfoModel->register($username,$password,$name);

dies::die($res);
