<?php

function mod_ulogin_networks($module_id, $cfg){

	$inUser = cmsUser::getInstance();
	$inPage = cmsPage::getInstance();
	$inCore = cmsCore::getInstance();

	if ($inUser->id <= 0) { return false; }

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

	require_once('/components/ulogin/ulogin.class.php');

	cmsCore::loadModel('ulogin');
	$model = new cms_model_ulogin();

	$inPage->addHeadJS( 'components/ulogin/js/ulogin.js' );
	$inPage->addHeadCSS( 'components/ulogin/css/ulogin.css' );
	$inPage->addHeadCSS( '/ulogin.ru/css/providers.css' );

	$componentConfig = $inCore->loadComponentConfig('ulogin');

	$uloginid = !empty($cfg['uloginid']) ? $cfg['uloginid'] : $componentConfig['uloginid'];
	$u_inc = !empty(uloginClass::$u_inc) ? uloginClass::$u_inc : 0;
	$id = 'ulogin_' . $uloginid . '_' . $u_inc;
	$redirect = urlencode(HOST . '/ulogin/login');
	$callback = 'uloginCallback';
	$add_str = $cfg['add_str'];
	$delete_str = $cfg['delete_str'];
	$networks = $model->getUloginUserNetworks($inUser->id);
	$hide_delete_str = !$networks ? ' style="display: none"' : '';

	uloginClass::$u_inc++;

	cmsPage::initTemplate('modules', $cfg['tpl'])->
		assign('id', $id)->
		assign('uloginid', $uloginid)->
		assign('redirect', $redirect)->
		assign('callback', $callback)->
		assign('networks', $networks)->
		assign('add_str', $add_str)->
		assign('delete_str', $delete_str)->
		assign('hide_delete_str', $hide_delete_str)->
		display($cfg['tpl']);

	return true;
}