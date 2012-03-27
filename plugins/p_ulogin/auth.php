<?php

session_start();

define("VALID_CMS", 1);
define('PATH', $_SERVER['DOCUMENT_ROOT']);
define('HOST', 'http://' . $_SERVER['HTTP_HOST']);

include(PATH . '/core/cms.php');

$inCore = cmsCore::getInstance();
$inCore->loadClass('config');
$inCore->loadClass('db');
$inCore->loadClass('page');
$inCore->loadClass('user');
$inCore->loadClass('plugin');

cmsCore::callEvent('ULOGIN_AUTH', array());

exit;

?>