<?php
/**
 * Created by PhpStorm.
 * User: yxs
 * Date: 2018-11-8
 * Time: 6:49 PM
 */
require_once __DIR__ . '/../../../../library/php/libs/Autoloader.php';//自动载入class

if(empty($_POST['page']))
    dies::error('参数不符');
$page = $_POST['page'];

$admin = models\admin\Admin_sessionModel::getInstance();
if(! $admin->isLogin(($adminId = $admin->getID())))
    dies::die($admin->notLogin());
if($adminId != 1)
    dies::error('权限不足');

$filtered = !empty($_POST['filtered']) && is_array($_POST['filtered']) ? $_POST['filtered'] : [];
$searched = !empty($_POST['searched']) && is_array($_POST['searched']) ? $_POST['searched'] : [];
$sorted = !empty($_POST['sorted']) && is_array($_POST['sorted']) && !empty($_POST['sorted']['key']) && !empty($_POST['sorted']['order']) && ($_POST['sorted']['order'] === 'desc' || $_POST['sorted']['order'] === 'asc')  ? $_POST['sorted'] : [];



use \models\admin\AdminInfoModel;
$adminInfoModel = new AdminInfoModel();
$res = $adminInfoModel->getList([AdminInfoModel::KEY_ID,AdminInfoModel::KEY_USERNAME,AdminInfoModel::KEY_NAME,AdminInfoModel::KEY_TIME,AdminInfoModel::KEY_USE],$page,$filtered,$searched,$sorted);

dies::die($res);
