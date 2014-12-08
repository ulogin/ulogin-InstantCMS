<?php

function mod_ulogin_messages($module_id, $cfg){

	$ulogin_message = $_SESSION['ulogin_message'];

	if (isset($ulogin_message)) {

		$inPage = cmsPage::getInstance();
		$inPage->addHeadJS( 'components/ulogin/js/ulogin_show_msg.js' );

		cmsPage::initTemplate('modules', $cfg['tpl'])->
			assign('messages', $ulogin_message)->
			display($cfg['tpl']);

		unset($_SESSION['ulogin_message']);

	}

	return true;
}