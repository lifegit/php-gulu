<?php

use \models\admin\AdminInfoModel;

require_once __DIR__ . '/../../../../../library/php/libs/Autoloader.php';//自动载入class


$admin = models\admin\Admin_sessionModel::getInstance();
if(! $admin->isLogin(($adminId = $admin->getID())))
    dies::die($admin->notLogin());


$fileManager = new \libs\FileManager();
$res_upload = $fileManager->fileUpload_avatar('admin');
if(! \Create::isSuccess($res_upload))
    dies::die($res_upload);



$adminInfoModel = new AdminInfoModel();
$res = $adminInfoModel->isExist([$adminId]);
if(! Create::isSuccess($res)){
    $fileManager->fileDel($res_upload[\libs\FileManager::RESULT_FILE][\libs\FileManager::KEY_FILE]);
    dies::die($res);
}



$res = $adminInfoModel->setInfo($adminId,[AdminInfoModel::KEY_AVATAR  => $res_upload[\libs\FileManager::RESULT_FILE][\libs\FileManager::KEY_URL] ]);
if(! Create::isSuccess($res)){
    $fileManager->fileDel($res_upload[\libs\FileManager::RESULT_FILE][\libs\FileManager::KEY_FILE]);
}


//点点 log

if(Create::isSuccess($res))
    $res['img'] = $res_upload[\libs\FileManager::RESULT_FILE][\libs\FileManager::KEY_URL];

dies::die($res);
