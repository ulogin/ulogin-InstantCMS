<?php

if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

function ulogin(){

	$inCore = cmsCore::getInstance();
	$inUser = cmsUser::getInstance();

	require_once('ulogin.class.php');

	$ulogin = new uloginClass();

	global $_LANG;

	$do = $inCore->do;

//============================================================================//

	if ($do == 'login'){
		$title = '';
		$msg = '';

		if (cmsCore::isAjax()) {
			$ulogin->doRedirect = false;
		}


		if ($inUser->isOnline($inUser->id)) {
			$msg = 'Аккаунт успешно добавлен';
		} else {
			$msg = 'Вход успешно выполнен';
		}

		$ulogin->uloginLogin($title, $msg);

		if(cmsCore::isAjax()) {
			exit;
		}

	}


	if ($do == 'delete_account'){

		$ulogin->deleteAccount();

	}

}
