<?php
/**
 * Created by PhpStorm.
 * User: yxs
 * Date: 2018-12-23
 * Time: 9:00 PM
 */



require_once __DIR__ . '/../../../../library/php/libs/Autoloader.php';//自动载入class

if( empty($_POST['p'])|| empty($_POST['t']))
    dies::error('参数不符');

$personName = $_POST['p'];
$time = $_POST['t'];

// 判断用户是否需要验证码
$verificationCodeModel = new models\communal\VerificationCodeModel($personName,\models\admin\AdminInfoModel::SIGN);
$isVC = $verificationCodeModel->isFrequently();

// 生产salt和code
use \models\communal\PasswordModel;
$passwordModel = new PasswordModel();
$res = $passwordModel->randSaltAndCode();


// 保存salt和code
use \models\communal\PersonCheckModel;
$userCheckModel = new PersonCheckModel("$personName:${res[PasswordModel::KEY_CODE]}", \models\admin\AdminInfoModel::SIGN);
$userCheckModel->setData([PersonCheckModel::KEY_SALT => $res[PasswordModel::KEY_SALT],PersonCheckModel::KEY_CODE => $res[PasswordModel::KEY_CODE]]);
$userCheckModel->getData([PersonCheckModel::KEY_SALT]);

// c : string   code 随机码
// s : string   salt 盐
// i : bool     是否需要图片验证码
dies::die(\Create::success(['c'=>$res[PasswordModel::KEY_CODE],'s'=>$passwordModel->saltEncrypt($res[PasswordModel::KEY_SALT],\configs\PassWord::RSA_SALT_HEADQUARTERS_PRIVATE_KEY),'i'=>$isVC]));
