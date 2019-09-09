<?php
/**
 * Created by PhpStorm.
 * User: yxs
 * Date: 2018-12-23
 * Time: 9:00 PM
 */

require_once __DIR__ . '/../../../../library/php/libs/Autoloader.php';//自动载入class

if( empty($_POST['p']) || empty($_POST['c']))
    dies::error('参数不符');

$personName = $_POST['p'];
$code = $_POST['c'];

$userCheckModel = new \models\communal\PersonCheckModel("$personName:$code", \models\admin\AdminInfoModel::SIGN);
if(! $userCheckModel->isEmpty())
    dies::error('登录失效,请重新点击登录',[\models\communal\PersonCheckModel::RESULT_PC => false]);


$vc = new \libs\verificationCode\VerificationCode();
$vc->buildCode();
$userCheckModel->setData([\models\communal\PersonCheckModel::KEY_VCODE => $vc->getCodeResult()]);

//echo "问题:".$vc->getCodeProblem()."答案:".$vc->getCodeResult();
$vc->showCodeGIF();
