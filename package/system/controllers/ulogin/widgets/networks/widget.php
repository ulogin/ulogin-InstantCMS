<?php
class widgetUloginNetworks extends cmsWidget {

	public static $u_inc;
	public $is_cacheable = false;

    public function run(){

	    if (!cmsUser::isLogged()){ return false; }

	    cmsCore::getController('ulogin');

	    $is_profile = ulogin::$is_profile;
	    ulogin::$is_profile = false;

	    if (!isset($is_profile) || $is_profile != true) {
		    if ($this->getOption('in_profile_only')) {
			    return false;
		    }
	    }


	    $ulogin_model = cmsCore::getModel('ulogin');
	    $networks = $ulogin_model->getUloginUserNetworks( cmsUser::getInstance()->id );

	    $editable = $this->getOption('editable');

	    if ($editable) {
		    self::$u_inc++;

		    $uloginid = $this->getOption('uloginid');

		    if (empty($uloginid)) {
			    $uloginid = cmsController::loadOptions( 'ulogin' )['uloginid'];
		    }

		    if (empty($uloginid)) {
			    $uloginid = '';
		    }

		    $u_id = 'ulogin_' . $uloginid . '_' . intval( self::$u_inc );

		    $callback = 'uloginCallback';
		    $redirect = urlencode(href_to_abs('ulogin','login'));

		    $add_str = $this->getOption('add_str');
		    $delete_str = $this->getOption('delete_str');


		    if (empty($uloginid)) {
			    $this->setTemplate('networks_default');
		    } else {
			    $this->setTemplate('networks_editable');
		    }

		    return array(
			    'id' => $u_id,
			    'uloginid' => $uloginid,
			    'callback' => $callback,
			    'redirect' => $redirect,
			    'networks' => $networks,
			    'add_str' => $add_str,
			    'delete_str'=> $delete_str,
		    );
	    }

        return array(
	        'networks' => $networks,
        );

    }

}
