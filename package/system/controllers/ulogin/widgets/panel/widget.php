<?php
class widgetUloginPanel extends cmsWidget {

	public static $u_inc;
	public $is_cacheable = false;

    public function run(){

	    cmsUser::getInstance();

	    if (cmsUser::isLogged()){ return false; }

	    $uloginid = $this->getOption('uloginid');

	    if (empty($uloginid)) {
		    $ulogin = cmsCore::getController('ulogin');
		    $uloginid = $ulogin->getOptions();
            $uloginid = !empty($uloginid['uloginid']) ? $uloginid['uloginid'] : '';
	    }

	    if (empty($uloginid)) {
		    $uloginid = '';
	    }

	    $u_id = 'ulogin_' . $uloginid . '_' . intval(self::$u_inc);

	    $callback = 'uloginCallback';
	    $redirect = urlencode(href_to_abs('ulogin','login'));

	    self::$u_inc++;

	    if (empty($uloginid)) {
		    $this->setTemplate('panel_default');
	    } else {
            $this->setTemplate('panel');
        }

        return array(
	        'id' => $u_id,
	        'uloginid' => $uloginid,
	        'callback' => $callback,
	        'redirect' => $redirect,
        );

    }

}
