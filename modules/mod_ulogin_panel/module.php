<?php

function mod_ulogin_panel($module_id, $cfg){

	$inUser = cmsUser::getInstance();
	$inPage = cmsPage::getInstance();
	$inCore = cmsCore::getInstance();

	if ($inUser->id > 0) { return false; }

	$inPage->addHeadJS( '/ulogin.ru/js/ulogin.js' );

	$ulogin_script = $_SESSION['ulogin_script'];

	if (isset($ulogin_script)) {
		if (isset($ulogin_script['token'])) {
			$params = $ulogin_script['token'];
			$params .= isset($ulogin_script['identity']) ? "','{$ulogin_script['identity']}" : '';
			echo "<script type='text/javascript'>uLogin.mergeAccounts('{$params}')</script>";
		}
		unset($_SESSION['ulogin_script']);
	}

	require_once(PATH.'/components/ulogin/ulogin.class.php');

	$inPage->addHeadJS( 'components/ulogin/js/ulogin.js' );
	$inPage->addHeadCSS( '/ulogin.ru/css/providers.css' );

	$componentConfig = $inCore->loadComponentConfig('ulogin');

	$uloginid = !empty($cfg['uloginid']) ? $cfg['uloginid'] : $componentConfig['uloginid'];
	$u_inc = !empty(uloginClass::$u_inc) ? uloginClass::$u_inc : 0;
	$id = 'ulogin_' . $uloginid . '_' . $u_inc;
	$redirect = urlencode(HOST . '/ulogin/login');
	$callback = 'uloginCallback';

	uloginClass::$u_inc++;

	cmsPage::initTemplate('modules', $cfg['tpl'])->
		assign('id', $id)->
		assign('uloginid', $uloginid)->
		assign('redirect', $redirect)->
		assign('callback', $callback)->
		display($cfg['tpl']);

	return true;
}