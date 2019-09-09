<?php

require_once __DIR__ . '/../../../../library/php/libs/Autoloader.php';//自动载入class

//登录
if( empty($_POST['username'])|| empty($_POST['password'])|| empty($_POST['code']))
    dies::error('参数不符');

$username = $_POST['username'];
$password = $_POST['password'];
$code = $_POST['code'];
$vc = !empty($_POST['vc']) ? $_POST['vc'] : null;

use \models\communal\PersonCheckModel;
$personCheckModel = new PersonCheckModel("$username:$code", \models\admin\AdminInfoModel::SIGN);
$resCheck = $personCheckModel->getData([PersonCheckModel::KEY_CODE,PersonCheckModel::KEY_SALT,PersonCheckModel::KEY_VCODE]);

// 验证salt
if(!$resCheck[PersonCheckModel::KEY_SALT] || !$resCheck[PersonCheckModel::KEY_CODE] || $resCheck[PersonCheckModel::KEY_CODE] != $code){
    dies::error('登录失效,请重新点击登录',[PersonCheckModel::RESULT_PC => false]);
}

use models\communal\VerificationCodeModel;
$verificationCodeModel = new VerificationCodeModel($username,\models\admin\AdminInfoModel::SIGN);
// 验证验证码
if($verificationCodeModel->isFrequently() && (!$vc || $resCheck[PersonCheckModel::KEY_VCODE] != $vc))
    dies::error('验证码错误!',[VerificationCodeModel::RESULT_VC => false]);



// 取出密码
use \models\admin\AdminInfoModel;
$headquartersInfoModel = new AdminInfoModel();
$temp = $headquartersInfoModel->getUserNameInfo($username,[AdminInfoModel::KEY_ID,AdminInfoModel::KEY_PASSWORD,AdminInfoModel::KEY_USE]);
! Create::isSuccess($temp) ? dies::error('账号或密码错误!') : $temp = $temp[AdminInfoModel::RESULT_INFO];
if(!$temp[AdminInfoModel::KEY_USE])
    dies::error('账号不可用!');



// 验证密码
$passwordModel = new \models\communal\PasswordModel();
$decryptPassword = $passwordModel->decryptPassword($password,$resCheck[PersonCheckModel::KEY_SALT],$resCheck[PersonCheckModel::KEY_CODE]);
if(! $passwordModel->check($decryptPassword, $temp[AdminInfoModel::KEY_PASSWORD])){
    $verificationCodeModel->addCount();
    dies::error('账号或密码错误!');
}



$personCheckModel->destroy();
$verificationCodeModel->delFrequently();

// 写session
$headquarters = models\admin\Admin_sessionModel::getInstance();
$headquarters->login($temp[AdminInfoModel::KEY_ID]);


$authority = $temp[AdminInfoModel::KEY_ID] === 1 ? ['authority'=>['staff'=>"set,get,add,del"]] : [];

dies::success($authority,'登录成功');
