<?php
require_once __DIR__ . '/../../../../library/php/libs/Autoloader.php';//自动载入class


$headquarters = models\admin\Admin_sessionModel::getInstance();
$headquarters->loginOut();


dies::success([],'退出成功!');
